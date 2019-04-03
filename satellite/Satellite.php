<?php

namespace satellite;

use exchanger\BatchResponse;
use exchanger\ExchangeInterface;
use exchanger\InvalidResponse;
use mq\QueryManagerInterface;
use program\FlyProgram;
use program\FlyProgramInterface;
use program\operations\BatchSetPayload;
use program\operations\LogDeltaOperation;
use program\operations\OperationInterface;
use program\operations\PayloadInterface;
use program\operations\SetPayload;
use telemetry\TelemetryInterface;

class Satellite
{
    /** @var ExchangeInterface $exchange */
    private $exchange;
    /** @var TelemetryInterface $telemetry */
    protected $telemetry;
    /** @var FlyProgram */
    private $flyProgram;
    /** @var QueryManagerInterface $mq */
    private $mq;
    /** @var SatelliteParametersInterface $params */
    private $params;

    /** @var string[] */
    private $telemetryParams = [];  //Cache

    /**
     * Satellite constructor.
     * @param TelemetryInterface $telemetry
     * @param ExchangeInterface $exchange
     * @param QueryManagerInterface $mq
     * @param SatelliteParametersInterface $params
     */
    public function __construct(
        TelemetryInterface $telemetry,
        ExchangeInterface $exchange,
        QueryManagerInterface $mq,
        SatelliteParametersInterface $params
    ) {
        $this->telemetry = $telemetry;
        $this->exchange  = $exchange;
        $this->mq        = $mq;
        $this->params    = $params;
        try {
            $this->exchange->initialize($this->mq,
                                        QueryManagerInterface::SND_QUEUE,
                                        QueryManagerInterface::RCV_QUEUE
            );
        } catch (\RuntimeException $e) {
            $this->criticalShutdown($e->getMessage(), 0);
        }

        foreach ($this->params->getParams() as $name => $param) {
            if ($param->isTelemetry()) {
                $this->telemetryParams[] = $name;
            }
        }
    }

    /**
     * @param FlyProgramInterface $program
     */
    public function run(FlyProgramInterface $program): void {
        $this->flyProgram = $program;
        $lastCountdown    = $this->flyProgram->getDelta();

        while (true) {
            $delta      = $this->flyProgram->getDelta();
            $operations = $this->flyProgram->getNext();
            if ($lastCountdown !== $delta) {
                $operations[]  = new LogDeltaOperation($this->flyProgram->getDelta());
                $lastCountdown = $delta;
            }
            if ($operations !== null) {
                $this->processOperations($operations);
            }

            if ($this->flyProgram->isFinished()) {
                $this->criticalShutdown('Fly program finished but not interrupted', 1);
            }

            usleep(2000);
        }
    }

    /**
     * @param OperationInterface[] $operations
     */
    private function processOperations(array $operations): void {
        $check         = [];
        $awaitResponse = 0;

        foreach ($operations as $op) {
            switch ($op->getType()) {
                case OperationInterface::START_PROGRAM:
                    $this->telemetry->info('Fly program started');
                    break;
                case OperationInterface::FINISH_PROGRAM:
                    $this->finish();
                    break;
                case OperationInterface::GET_PARAM:
                    $this->requestApi($op->getPayload());
                    $awaitResponse++;
                    break;
                case OperationInterface::BATCH_SET:
                    /** @var BatchSetPayload $payload */
                    $payload = $op->getPayload();
                    $this->requestApi($payload);
                    $awaitResponse++;
                    array_push($check, ...$payload->variables);
                    break;
                case OperationInterface::CHECK_PARAM:
                case OperationInterface::SEND_TELEMETRY:
                    $check[] = $op;
                    break;
                case OperationInterface::LOG_DELTA:
                    $this->telemetry->info('Delta: ' . $this->flyProgram->getDelta());
                    break;
                case OperationInterface::INFO_MESSAGE:
                    $this->telemetry->info($op->getPayload());
                    break;
                default:
                    $this->telemetry->error('Unknown operation ' . $op->getType());
            }
        }

        if ($awaitResponse === 0) {
            return;
        }
        /*
         *Lets imagine that exchanger works asynchronously in separate process
         */
        try {
            $this->exchange->processTasks();
        } catch (\Exception $e) {
            $this->criticalShutdown('Internal error: ' . $e->getMessage(), 2);
        }

        $parameters = $this->getResponses($awaitResponse);
        $this->checkResponse($parameters, $check);
    }

    private function requestApi(PayloadInterface $payload): void {
        $this->mq->put(QueryManagerInterface::SND_QUEUE, $payload);
    }

    private function getResponses(int $awaitCnt): SatelliteParametersInterface {
        $deadLine    = microtime(true) + 0.15;
        $receivedCnt = 0;
        $received    = [];
        while (microtime(true) < $deadLine && $receivedCnt < $awaitCnt) {
            $message = $this->mq->get(QueryManagerInterface::RCV_QUEUE);
            if (!$message instanceof BatchResponse) {
                $this->telemetry->error('Internal error. Wrong response received: ' . \gettype($message));
            } else if ($message !== null) {
                $received[] = $message;
                $receivedCnt++;
            }
        }

        return $this->parseResponse($received);
    }

    /**
     * @param BatchResponse[] $received
     * @return SatelliteParametersInterface
     */
    private function parseResponse(array $received): SatelliteParametersInterface {
        $receivedParams = new SatelliteParameters();

        foreach ($received as $batchResponse) {
            foreach ($batchResponse->getResponses() as $response) {
                if ($response instanceof InvalidResponse) {
                    $this->telemetry->error('Invalid response: ' . $response->variable);
                    continue;
                }
                $param = $this->params[ $response->variable ];
                if ($param !== null) {
                    if ($param->set($response->value) === false) {
                        $this->telemetry->error('Parameter ' . $response->variable . ' is out of range: ' . $response->value);
                    }
                    $param->setNewValue($response->set);
                    $receivedParams->add($response->variable, $param);
                }
            }
        }

        return $receivedParams;
    }

    /**
     * @param SatelliteParametersInterface $params
     * @param OperationInterface[] $check
     */
    private function checkResponse(SatelliteParametersInterface $params, array $check): void {
        foreach ($check as $op) {
            switch ($op->getType()) {
                case OperationInterface::SET_PARAM:
                    $this->validateSet($op, $params);
                    break;
                case OperationInterface::CHECK_PARAM:
                    $this->validateCheck($op, $params);
                    break;
                case OperationInterface::SEND_TELEMETRY:
                    $this->sendTelemetry($params);
                    break;
                default:
                    $this->telemetry->error('Internal error. Invalid check task');
            }
        }
    }

    private function validateSet(OperationInterface $op, SatelliteParametersInterface $params): void {
        /** @var SetPayload $payload */
        $payload = $op->getPayload();
        $param   = $params[ $payload->variable ];
        if ($param === null || $param->getNewValue() !== $payload->set) {
            $this->telemetry->error('Unexpected response for operation ' . $op->getId());
            if ($op->isCritical()) {
                $this->criticalShutdown('Critical operation failed. Shutdown immediately.', 11);
            }
        }
    }

    private function validateCheck(OperationInterface $op, SatelliteParametersInterface $params): void {
        /** @var SetPayload $payload */
        $payload = $op->getPayload();
        $param   = $params[ $payload->variable ];
        if ($param === null || $param->get() !== $payload->set) {
            $result = $param !== null ? $param->get() : 'NULL';
            $this->telemetry->error('Unexpected operation' . $op->getId() . ' result. Expected ' . $payload->set . ' received ' . $result);
            if ($op->isCritical()) {
                $this->criticalShutdown('Critical operation failed. Shutdown immediately.', 12);
            }
        }
    }

    private function sendTelemetry(SatelliteParametersInterface $params): void {
        $out = [];
        foreach ($this->telemetryParams as $name) {
            $out[ $name ] = $params[ $name ]->get();
        }
        $this->telemetry->sendValues($out);
    }

    protected function finish(): void {
        $this->telemetry->info('Fly program complete');
        exit(0);
    }

    protected function criticalShutdown(string $text, int $code): void {
        $this->telemetry->error($text);
        exit($code);
    }
}