<?php

namespace satellite;
interface ParameterInterface
{
    public function __construct(int $min, int $max, $telemetry = true);

    public function get(): int;

    public function set(int $value): bool;

    public function validate(int $check = null): bool;

    public function isTelemetry(): bool;
}