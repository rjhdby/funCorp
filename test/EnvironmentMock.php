<?php

namespace test;

use environment\Environment;

class EnvironmentMock extends Environment
{
    public function getFlyProgramJson(): string {
        $time = time() + 3;

        return str_replace('"time"', $time, parent::getFlyProgramJson());
    }
}