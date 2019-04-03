<?php

namespace test;

use telemetry\Telemetry;

class TelemetryMock extends Telemetry
{
    protected function writeToStdout(string $text): void {
        echo $text;
    }

    protected function writeToStderr(string $text): void {
        echo $text;
    }
}