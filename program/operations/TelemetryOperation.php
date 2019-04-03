<?php

namespace program\operations;

class TelemetryOperation extends InternalOperation
{
    public function __construct(int $deltaT) {
        parent::__construct(self::SEND_TELEMETRY, $deltaT);
    }
}