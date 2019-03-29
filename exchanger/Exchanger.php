<?php

namespace exchanger;

use environment\EnvironmentInterface;
use mq\QueryManagerInterface;
use program\Operation;
use telemetry\TelemetryInterface;

class Exchanger implements ExchangeInterface
{
    private $exchangeUri;

    /** @var QueryManagerInterface $mq */
    protected $mq;
    protected $sndQueue;
    protected $rcvQueue;
    private   $telemetry;

    /**
     * Reset values in database
     */
    public function reset(): void {
        $curl = curl_init($this->exchangeUri.'reset');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);
        curl_exec($curl);
        curl_close($curl);
    }

    public function __construct(EnvironmentInterface $env, TelemetryInterface $telemetry) {
        $this->exchangeUri = $env->getExchangeUri() . '/settings/';
        $this->telemetry   = $telemetry;
    }

    public function initialize(QueryManagerInterface $mq,
                               string $sndQueue,
                               string $rcvQueue): void {
        $this->mq       = $mq;
        $this->sndQueue = $sndQueue;
        $this->rcvQueue = $rcvQueue;
    }

    public function processTasks(): void {
        while ($task = $this->mq->get($this->sndQueue)) {
            if (\is_array($task) && \is_object($task[0])) {
                if ($this->telemetry) {
                    $this->telemetry->log(json_encode(['PATCH' => $task], true));
                }
                $this->mq->put($this->rcvQueue, $this->patch($task));
            } else {
                if ($this->telemetry) {
                    $this->telemetry->log(json_encode(['GET' => $task], true));
                }
                $this->mq->put($this->rcvQueue, $this->get($task));
            }
        }
    }

    /**
     * @param Operation[] $operations
     * @return Response[]
     */
    private function patch(array $operations): array {
        $critical = false;

        $curl = curl_init($this->exchangeUri);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($operations, true));

        return $this->execute($curl, $critical);
    }

    /**
     * @param array $params
     * @return Response[]
     */
    private function get(array $params): array {
        $curl = curl_init($this->exchangeUri . implode(',', $params));

        return $this->execute($curl);
    }

    /**
     * @param resource $curl
     * @param bool $critical
     * @return Response[]
     */
    private function execute($curl, $critical = false): array {
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);

        $result   = curl_exec($curl);
        $response = json_decode($result, true);
        $err      = curl_error($curl);
        curl_close($curl);
        if (!\is_array($response)) {
            throw new \RuntimeException("exchanger request error. $err.", $critical);
        }

        return $this->prepareResponse($response);
    }

    /**
     * @param array $response
     * @return Response[]
     */
    protected function prepareResponse(array $response): array {
        $out = [];
        foreach ($response as $key => $value) {
            if (!isset($value['set'], $value['value'])
                || !\is_int($value['set'])
                || !\is_int($value['value'])
            ) {
                throw new \RuntimeException('Invalid request response');
            }
            $out[] = new Response((string)$key, $value['set'], $value['value']);
        }

        return $out;
    }
}