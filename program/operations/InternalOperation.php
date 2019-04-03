<?php

namespace program\operations;

interface BaseOperationInterface
{
    public const START_PROGRAM  = 'start';
    public const FINISH_PROGRAM = 'finish';
    public const SET_PARAM      = 'set';
    public const GET_PARAM      = 'get';
    public const CHECK_PARAM    = 'check';

    public const INFO_MESSAGE   = 'infoMessage';
    public const SEND_TELEMETRY = 'sendTelemetry';
    public const UPDATE_PARAMS  = 'updateParams';

    public function getType(): string;

    public function getDelta(): int;
}