<?php

namespace program\operations;

class StartOperation extends InternalOperation
{
    public function __construct() {
        parent::__construct(self::START_PROGRAM, 0);
    }
}