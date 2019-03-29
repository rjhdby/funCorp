<?php

namespace environment;

use satellite\SatelliteParametersInterface;

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
    public function getFlyProgramJson(): string {
        $src = $this->getEnv(self::FLIGHT_PLAN_ENV);
        if ($src === false) {
            throw new \RuntimeException('Flight program location is not set');
        }
        $result = file_get_contents($src);
        if ($result === false) {
            throw new \RuntimeException('Flight program not found');
        }

        return $result;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getExchangeUri(): string {
        $result = $this->getEnv(self::EXCHANGE_URI_ENV);
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
        $result = $this->getEnv(self::TELEMETRY_FREQ_ENV);
        if ($result === false || (int)$result < 1) {
            throw new \RuntimeException('Telemetry frequency is not set or invalid');
        }

        return (int)$result;
    }

    public function getDefaultTelemetryFreq(): int {
        return self::DEFAULT_TELEMETRY_FREQ;
    }

    private function getEnv(string $env): string {
        return getenv($env);
    }

    /**
     * @param string $class
     * @param string $json
     * @return SatelliteParametersInterface
     */
    public function getSatelliteParameters(string $class, string $json): SatelliteParametersInterface {
        $raw = json_decode($json, true);
        /** @var SatelliteParametersInterface $params */
        $params = new $class();
        foreach ($raw as $name => $param) {
            if (!isset($param['min'], $param['max'], $param['telemetry'])) {
                throw new \RuntimeException('Invalid parameters');
            }
            if (\is_string($name)
                && \is_int($param['max'])
                && \is_bool($param['telemetry'])
                && \is_int($param['min'])) {
                $params->create($name, $param['min'], $param['max'], $param['telemetry']);
            } else {
                throw new \RuntimeException('Invalid parameters');
            }
        }

        return $params;
    }
}