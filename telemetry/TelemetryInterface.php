<?php

namespace telemetry;
interface TelemetryInterface
{
    public function error(string $message): void;

    public function info(string $message): void;

    public function sendTelemetry(string $message): void;

    public function log(string $message): void;
}