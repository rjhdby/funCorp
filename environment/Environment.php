<?php

namespace environment;

class Environment implements EnvironmentInterface
{
    private const FLIGHT_PLAN_ENV    = 'FLIGHT_PROGRAM';
    private const EXCHANGE_URI_ENV   = 'EXCHANGE_URI';
    private const TELEMETRY_FREQ_ENV = 'TELEMETRY_FREQ';

    private const DEFAULT_TELEMETRY_FREQ = 10;

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getFlyProgramLocation(): string {
        $src = getenv(self::FLIGHT_PLAN_ENV);
        if ($src === false) {
            throw new \RuntimeException('Flight program location is not set');
        }

        return $src;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getExchangeUri(): string {
        $result = getenv(self::EXCHANGE_URI_ENV);
        if ($result === false) {
            throw new \RuntimeException('Exchange URI is not set');
        }

        return $result;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getTelemetryFreq(): int {
        $result = getenv(self::TELEMETRY_FREQ_ENV);
        if ($result === false || (int)$result < 1) {
            return self::DEFAULT_TELEMETRY_FREQ;
        }

        return (int)$result;
    }
}