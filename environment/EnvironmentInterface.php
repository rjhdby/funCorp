<?php

namespace environment;

use satellite\SatelliteParametersInterface;

interface EnvironmentInterface
{
    public function getFlyProgramJson(): string;

    public function getExchangeUri(): string;

    public function getTelemetryFreq(): int;

    public function getDefaultTelemetryFreq(): int;

    public function getSatelliteParameters(string $params, string $json): SatelliteParametersInterface;
}