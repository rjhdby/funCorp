<?php

namespace environment;

interface EnvironmentInterface
{
    public function getFlyProgramLocation(): string;

    public function getExchangeUri(): string;

    public function getTelemetryFreq(): int;
}