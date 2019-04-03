<?php

namespace program\operations;

class StartOperation extends InternalOperation
{
    public function __construct() {
        $this->type   = self::START_PROGRAM;
        $this->deltaT = 0;
    }
}