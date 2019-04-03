<?php

namespace exchanger;

use environment\EnvironmentInterface;
use mq\QueryManagerInterface;
use program\operations\BatchSetPayload;
use program\operations\GetPayload;
use program\operations\PayloadInterface;
use program\operations\SetPayload;
use telemetry\TelemetryInterface;

class Exchanger implements ExchangeInterface
{
    private $exchangeUri;

    /** @var QueryManagerInterface $mq */
    protected $mq;
    protected $sndQueue;
    private   $telemetry;
    private   $rcvQueue;

    private const END_POINT = '/settings/';

    /**
     * Reset values in database. Test purpose.
     */

    public function reset(): void {
        $curl = curl_init($this->exchangeUri . 'reset');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);
        curl_exec($curl);
        curl_close($curl);
    }

    public function __construct(EnvironmentInterface $env, ?TelemetryInterface $telemetry = null) {
        $this->exchangeUri = $env->getExchangeUri() . self::END_POINT;
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
        /** @var PayloadInterface $request */
        while ($request = $this->mq->get($this->sndQueue)) {
            if (!(\is_object($request) && $request instanceof PayloadInterface)) {
                throw new \InvalidArgumentException('Unknown request. PayloadInterface expected but ' . \gettype($request) . ' received');
            }

            if ($request instanceof BatchSetPayload) {
                $payload = $this->makePatchDataSet($request);
                $this->log('PATCH', $payload);
                $this->mq->put($this->rcvQueue, $this->patchRequest($payload));
            } else if ($request instanceof GetPayload) {
                $payload = $this->makeGetDataSet($request);
                $this->log('GET', $payload);
                $this->mq->put($this->rcvQueue, $this->getRequest($payload));
            } else {
                throw new \InvalidArgumentException('Unknown payload type ' . \gettype($request) . ' received');
            }
        }
    }

    /**
     * @param BatchSetPayload $batchPayload
     * @return string
     */
    private function makePatchDataSet(BatchSetPayload $batchPayload): string {
        $out = [];
        foreach ($batchPayload->variables as $op) {
            /** @var SetPayload $payload */
            $payload                   = $op->getPayload();
            $out[ $payload->variable ] = $payload->set;
        }

        return json_encode($out, true);
    }

    /**
     * @param GetPayload $getPayload
     * @return string
     */
    private function makeGetDataSet(GetPayload $getPayload): string {
        return implode(',', $getPayload->variables);
    }

    private function log($type, $payload): void {
        if ($this->telemetry !== null) {
            $this->telemetry->log($type . ': ' . $payload);
        }
    }

    /**
     * @param string $payload
     * @return BatchResponse
     */
    private function patchRequest(string $payload): BatchResponse {
        $opts = [
            CURLOPT_URL           => $this->exchangeUri,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS    => $payload,
        ];

        return $this->execute($opts);
    }

    /**
     * @param string $payload
     * @return BatchResponse
     */
    private function getRequest(string $payload): BatchResponse {
        return $this->execute([CURLOPT_URL => $this->exchangeUri . $payload]);
    }

    /**
     * @param array $opts
     * @return BatchResponse
     */
    private function execute(array $opts): BatchResponse {
        $curl = curl_init();
        foreach ($opts as $key => $value) {
            curl_setopt($curl, $key, $value);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);

        $result = curl_exec($curl);
        $data   = json_decode($result, true);
        $err    = curl_error($curl);
        curl_close($curl);

        $response = new BatchResponse();
        if (!\is_array($data)) {
            $response->add(new InvalidResponse('Error connecting API: ' . $err));
        } else {
            $this->fillResponse($response, $data);
        }

        return $response;
    }

    /**
     * @param BatchResponse $response
     * @param $data
     */
    private function fillResponse(BatchResponse $response, $data): void {
        foreach ($data as $key => $value) {
            if (!isset($value['set'], $value['value'])
                || !\is_int($value['set'])
                || !\is_int($value['value'])
            ) {
                $response->add(new InvalidResponse(json_encode([$key => $value], true)));
            } else {
                $response->add(new Response($key, $value['set'], $value['value']));
            }
        }
    }
}