<?php

namespace satellite;
interface ParameterInterface
{
    public function get();

    public function set($value): bool;

    public function getNewValue();

    public function setNewValue($value): void;

    public function validate($check = null): bool;

    public function isTelemetry(): bool;

    public function getSpeed(): int;
}