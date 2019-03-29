<?php

namespace test;

use environment\EnvironmentInterface;
use exchanger\Exchanger;
use exchanger\Response;
use program\Operation;
use satellite\SatelliteParametersInterface;
use telemetry\TelemetryInterface;

class ExchangerMock extends Exchanger
{
    /** @var SatelliteParametersInterface */
    public $params;

    /** @var TelemetryInterface $telemetry */
    private $telemetry;
    private $timeouts = [];
    private $failed   = [];
    private $tainted  = [];

    /**
     * ExchangerMock constructor.
     * @param EnvironmentInterface $env
     * @param TelemetryInterface $telemetry
     */
    public function __construct(EnvironmentInterface $env, TelemetryInterface $telemetry) {
        $this->telemetry = $telemetry;
        parent::__construct($env);
    }

    public function setTimeouts(array $timeouts): void {
        $this->timeouts = $timeouts;
    }

    public function setFailed(array $failed): void {
        $this->failed = $failed;
    }

    public function setTainted(array $tainted): void {
        $this->tainted = $tainted;
    }

    public function processTasks(): void {
        while ($task = $this->mq->get($this->sndQueue)) {
            if (\is_array($task) && \is_object($task[0])) {
                $this->telemetry->log(json_encode(['PATCH' => $task], true));
                $response = $this->patch($task);
                if ($response !== null) {
                    $this->mq->put($this->rcvQueue, $response);
                }
            } else {
                $this->telemetry->log(json_encode(['GET' => $task], true));
                $this->mq->put($this->rcvQueue, $this->get($task));
            }
        }
    }

    /**
     * @param Operation[] $params
     * @return Response[]
     */
    private function patch(array $params): ?array {
        $response = [];
        foreach ($params as $param) {
            if (\in_array($param->id, $this->timeouts, true)) {
                usleep(100000);
                throw new \RuntimeException('Exchanger request error. Timeout', $param->critical);
            }
            if (\in_array($param->id, $this->failed, true)) {
                return null;
            }
            if (\in_array($param->id, $this->tainted, true)) {
                $param->value = -1000;
            }
            $response[ $param->variable ] = [
                'set'   => $param->value,
                'value' => $param->value
            ];
        }

        return $this->prepareResponse($response);
    }

    private function get(array $params): array {
        $response = [];
        foreach ($params as $param) {
            $response[ $param ] = [
                'set'   => $this->params->get($param),
                'value' => $this->params->get($param)
            ];
        }

        return $this->prepareResponse($response);
    }
}