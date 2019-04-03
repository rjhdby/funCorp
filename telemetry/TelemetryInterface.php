<?php

namespace telemetry;
interface TelemetryInterface
{
    public function error(string $message): void;

    public function info(string $message): void;

    public function sendValues(array $values): void;

    public function log(string $message): void;
}