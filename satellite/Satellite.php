<?php

namespace satellite;

use environment\EnvironmentInterface;
use exchanger\ExchangeInterface;
use exchanger\Response;
use mq\QueryManager;
use mq\QueryManagerInterface;
use program\FlyProgram;
use program\Operation;
use telemetry\TelemetryInterface;

class Satellite
{
    /** @var SatelliteParametersInterface $params */
    public $params;

    private   $telemetryFreq;
    private   $exchange;
    protected $telemetry;

    private $telemetryParams;
    private $allParams;

    /** @var FlyProgram */
    private $flyProgram;
    private $nextTelemetrySend;

    /** @var Operation[] */
    private $awaitTasks = [];

    /** @var QueryManagerInterface $mq */
    private $mq;

    private const FINISH_PROGRAM   = 'finishProgram';
    private const INFO_MESSAGE     = 'infoMessage';
    private const CHECK_COMPLETION = 'checkCompletion';
    private const SEND_TELEMETRY   = 'sendTelemetry';
    private const UPDATE_PARAMS    = 'updateParams';

    /**
     * Satellite constructor.
     * @param SatelliteParametersInterface $params
     * @param TelemetryInterface $telemetry
     * @param ExchangeInterface $exchange
     * @param QueryManagerInterface $mq
     */
    public function __construct(
        SatelliteParametersInterface $params,
        TelemetryInterface $telemetry,
        ExchangeInterface $exchange,
        QueryManagerInterface $mq
    ) {
        $this->setParams($params);
        $this->telemetry = $telemetry;
        $this->exchange  = $exchange;
        $this->mq        = $mq;
    }

    /**
     * @param SatelliteParametersInterface $params
     */
    public function setParams(SatelliteParametersInterface $params): void {
        $this->params = $params;
        foreach ($params->getNames() as $value) {
            if ($params->isImportant($value)) {
                $this->telemetryParams[] = $value;
            }
            $this->allParams[] = $value;
        }
    }

    /**
     * @param EnvironmentInterface $env
     */
    public function initialize(EnvironmentInterface $env): void {
        try {
            $this->exchange->initialize($this->mq,
                                        QueryManagerInterface::SND_QUEUE,
                                        QueryManagerInterface::RCV_QUEUE
            );
        } catch (\RuntimeException $e) {
            $this->criticalShutdown($e->getMessage(), 0);
        }
        try {
            $this->telemetryFreq = $env->getTelemetryFreq();
        } catch (\RuntimeException $e) {
            $this->telemetry->error($e->getMessage());
            $this->telemetryFreq = $env->getDefaultTelemetryFreq();
            $this->telemetry->info('Default frequency value is used: ' . $this->telemetryFreq . ' sec.');
        }
    }

    /**
     * @param FlyProgram $program
     */
    public function run(FlyProgram $program): void {
        $this->flyProgram        = $program;
        $lastCountdown           = $this->flyProgram->delta();
        $this->nextTelemetrySend = $lastCountdown;

        while (true) {
            $delta = $this->flyProgram->delta();

            if ($lastCountdown !== $delta) {
                if ($delta === 0) {
                    $this->telemetry->info('Fly program started');
                }
                $this->enqueueInfo('Delta ' . $delta);
                $lastCountdown = $delta;
            }
            $this->enqueueTelemetry();
            $this->enqueueAwaitingCheck();
            $this->processRequests();
            $this->processControls();
            $this->processTelemetry();
            $this->enqueueIfFinished();
            usleep(10000);
        }
    }

    private function enqueueTelemetry(): void {
        if ($this->nextTelemetrySend === $this->flyProgram->delta()) {
            $this->nextTelemetrySend += $this->telemetryFreq;
            $this->enqueueUpdateParams();
            $this->mq->put(QueryManagerInterface::SATELLITE_CTRL,
                           new SatelliteCommand(self::SEND_TELEMETRY, $this->collectTelemetry())
            );
        }
    }

    private function processRequests(): void {
        $batch = $this->flyProgram->getNext();
        if ($batch !== null) {
            $this->mq->put(QueryManager::SND_QUEUE, $batch);
            foreach ($batch as $task) {
                $this->holdForCheck($task);
            }
        }
        $this->doRequests();
    }

    private function doRequests(): void {
        try {
            $this->exchange->processTasks();
            $this->processResponses();
        } catch (\RuntimeException $e) {
            $this->failedTaskProcess($e->getCode(), $e->getMessage());
        }
    }

    private function enqueueAwaitingCheck(): void {
        if (isset($this->awaitTasks[ $this->flyProgram->delta() ])) {
            $this->enqueueUpdateParams();
            $this->mq->put(QueryManagerInterface::SATELLITE_CTRL,
                           new SatelliteCommand(self::CHECK_COMPLETION, $this->flyProgram->delta())
            );
        }
    }

    private function enqueueIfFinished(): void {
        if (empty($this->awaitTasks) && $this->flyProgram->isFinished()) {
            $this->mq->put(QueryManagerInterface::SATELLITE_CTRL,
                           new SatelliteCommand(self::FINISH_PROGRAM)
            );
        }
    }

    private function enqueueInfo(string $text): void {
        $this->mq->put(QueryManagerInterface::SATELLITE_CTRL,
                       new SatelliteCommand(self::INFO_MESSAGE, $text)
        );
    }

    private function processTelemetry(): void {
        while ($payload = $this->mq->get(QueryManagerInterface::TELEMETRY_SND)) {
            $this->telemetry->sendTelemetry($payload);
        }
    }

    private function processResponses(): void {
        /** @var Response[] $result */
        while ($result = $this->mq->get(QueryManagerInterface::RCV_QUEUE)) {
            $this->telemetry->log(json_encode(['Response' => $result], true));
            $this->applyResponse($result);
        }
    }

    /**
     * @param Response[] $responses
     */
    private function applyResponse(array $responses): void {
        /** @var Response $response */
        foreach ($responses as $response) {
            if (!$this->params->set($response->variable, $response->value)) {
                $this->telemetry->error("Parameter $response->variable is out of range: $response->value");
            }
        }
    }

    private function processControls(): void {
        /** @var SatelliteCommand $task */
        while ($task = $this->mq->get(QueryManagerInterface::SATELLITE_CTRL)) {
            if (!$task instanceof SatelliteCommand) {
                $this->telemetry->error('Invalid command received');
                continue;
            }
            switch ($task->command) {
                case self::UPDATE_PARAMS:
                    $this->enqueueUpdateParams();
                    break;
                case self::SEND_TELEMETRY:
                    $this->mq->put(QueryManagerInterface::TELEMETRY_SND, $task->payload);
                    break;
                case self::CHECK_COMPLETION:
                    $this->checkTasksCompletion($task->payload);
                    break;
                case self::INFO_MESSAGE:
                    $this->telemetry->info($task->payload);
                    break;
                case self::FINISH_PROGRAM:
                    $this->finish();
                    break;
                default:
                    $this->telemetry->error('Unknown command received');
            }
        }
    }

    /**
     * @param Operation $task
     */
    private function holdForCheck(Operation $task): void {
        $checkDelta = $task->deltaT + $task->timeout;
        if (!isset($this->awaitTasks[ $checkDelta ])) {
            $this->awaitTasks[ $checkDelta ] = [];
        }
        $this->awaitTasks[ $checkDelta ][ $task->id ] = $task;
    }

    /**
     * @param bool $critical
     * @param string $error
     */
    private function failedTaskProcess(bool $critical, string $error): void {
        if ($critical) {
            $this->criticalShutdown("$error. Shutdown on critical operation", 11);
        } else {
            $this->telemetry->error("$error. Operation status is unknown");
        }
    }

    /**
     * @param int $currentDelta
     */
    private function checkTasksCompletion(int $currentDelta): void {
        /** @var Operation $task */
        foreach ($this->awaitTasks[ $currentDelta ] as $task) {
            $this->checkOperationResult($task);
        }
        unset($this->awaitTasks[ $currentDelta ]);
    }

    private function enqueueUpdateParams(): void {
        $this->mq->put(QueryManagerInterface::SND_QUEUE, $this->allParams);
    }

    /**
     * @param Operation $task
     */
    private function checkOperationResult($task): void {
        if (!$this->params->validate($task->variable)) {
            if ($task->critical) {
                $this->criticalShutdown("Parameter $task->variable is out of range: {$this->params->get($task->variable)}", 12);
            } else {
                $this->telemetry->error("Parameter $task->variable is out of range: {$this->params->get($task->variable)}");
            }
        } elseif ($this->params->get($task->variable) !== $task->value) {
            if ($task->critical) {
                $this->criticalShutdown("Parameter $task->variable is wrong. Actual: {$this->params->get($task->variable)}, expect:$task->value", 12);
            } else {
                $this->telemetry->error("Parameter $task->variable is wrong. Actual: {$this->params->get($task->variable)}, expect:$task->value");
            }
        }
    }

    protected function finish(): void {
        $this->telemetry->info('Fly program complete');
        exit(0);
    }

    /**
     * @return string
     */
    private function collectTelemetry(): string {
        $result = [];
        foreach ($this->telemetryParams as $paramName) {
            $result[] = $paramName . '=' . $this->params->get($paramName);
        }

        return implode('&', $result);
    }

    protected function criticalShutdown(string $text, int $code): void {
        $this->telemetry->error($text);
        exit($code);
    }
}