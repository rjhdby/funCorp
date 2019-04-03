<?php

namespace satellite;

interface SatelliteParametersInterface extends \ArrayAccess
{
    /**
     * @param string $name
     * @param ParameterInterface $parameter
     * @return SatelliteParametersInterface
     */
    public function add(string $name, ParameterInterface $parameter): SatelliteParametersInterface;

    /**
     * @return string[]
     */
    public function getNames(): array;

    /**
     * @return ParameterInterface[]
     */
    public function getParams(): array;

    public function offsetGet($offset): ?ParameterInterface;
}