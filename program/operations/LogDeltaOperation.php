<?php

namespace program\operations;

class LogDeltaOperation extends InternalOperation
{
    /**
     * LogDeltaOperation constructor.
     * @param int $deltaT
     */
    public function __construct(int $deltaT) {
        parent::__construct(self::LOG_DELTA, $deltaT);
    }
}