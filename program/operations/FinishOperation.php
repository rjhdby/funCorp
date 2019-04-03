<?php

namespace program\operations;

class FinishOperation extends InternalOperation
{
    /**
     * StartOperation constructor.
     * @param int $deltaT
     */
    public function __construct(int $deltaT) {
        parent::__construct(self::FINISH_PROGRAM, $deltaT);
    }
}