<?php

namespace satellite;

interface SatelliteParametersInterface
{
    /**
     * @param string $name
     * @param ParameterInterface $parameter
     * @return SatelliteParametersInterface
     */
    public function add(string $name, ParameterInterface $parameter): SatelliteParametersInterface;

    /**
     * @param string $name
     * @param int $min
     * @param int $max
     * @param bool $telemetry
     * @return SatelliteParametersInterface
     */
    public function create(string $name, int $min, int $max, bool $telemetry): SatelliteParametersInterface;

    /**
     * @param string $name
     * @param int $value
     * @return bool
     */
    public function set(string $name, int $value): bool;

    /**
     * @param string $name
     * @return int
     */
    public function get(string $name): int;

    /**
     * @param string $name
     * @param int|null $value
     * @return bool
     */
    public function validate(string $name, int $value = null): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function isImportant(string $name): bool;

    /**
     * @return array
     */
    public function getNames(): array;
}