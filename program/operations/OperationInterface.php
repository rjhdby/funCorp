<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

interface OperationInterface
{
    public const START_PROGRAM  = 'start';
    public const FINISH_PROGRAM = 'finish';
    public const SET_PARAM      = 'set';
    public const BATCH_SET      = 'batch';
    public const GET_PARAM      = 'get';
    public const CHECK_PARAM    = 'check';
    public const LOG_DELTA      = 'delta';
    public const INFO_MESSAGE   = 'infoMessage';
    public const SEND_TELEMETRY = 'sendTelemetry';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return int
     */
    public function getDelta(): int;

    /**
     * @return mixed
     */
    public function getPayload(): PayloadInterface;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool;

    /**
     * @return bool
     */
    public function isCritical(): bool;
}