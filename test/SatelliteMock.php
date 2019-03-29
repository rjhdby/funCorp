<?php

namespace test;

use satellite\Satellite;

class SatelliteMock extends Satellite
{
    protected function criticalShutdown(string $text, int $code): void {
        $this->telemetry->error($text);
        throw new \RuntimeException("Program aborted with code $code");
    }

    protected function finish(): void {
        throw new \RuntimeException('Fly program complete');
    }
}