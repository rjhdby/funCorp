<?php

namespace satellite;

class SatelliteParameters implements SatelliteParametersInterface
{
    /** @var Parameter[] */
    private $parameters = [];

    /**
     * @param string $name
     * @param ParameterInterface $parameter
     * @return SatelliteParametersInterface
     */
    public function add(string $name, ParameterInterface $parameter): SatelliteParametersInterface {
        $this->parameters[ $name ] = $parameter;

        return $this;
    }

    /**
     * @param string $name
     * @param int $value
     * @return bool
     */
    public function set(string $name, int $value): bool {
        if (!isset($this->parameters[ $name ])) {
            throw new \RuntimeException("Unknown parameter $name");
        }

        return $this->parameters[ $name ]->set($value);
    }

    /**
     * @param string $name
     * @return int
     */
    public function get(string $name): int {
        if (!isset($this->parameters[ $name ])) {
            throw new \RuntimeException("Unknown parameter $name");
        }

        return $this->parameters[ $name ]->get();
    }

    /**
     * @param string $name
     * @param int|null $value
     * @return bool
     */
    public function validate(string $name, int $value = null): bool {
        if (!isset($this->parameters[ $name ])) {
            throw new \RuntimeException("Unknown parameter $name");
        }

        return $this->parameters[ $name ]->validate($value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isImportant(string $name): bool {
        return $this->parameters[ $name ]->isTelemetry();
    }

    /**
     * @return array
     */
    public function getNames(): array {
        return array_keys($this->parameters);
    }

    /**
     * @param string $name
     * @param int $min
     * @param int $max
     * @param bool $telemetry
     * @return SatelliteParametersInterface
     */
    public function create(string $name, int $min, int $max, bool $telemetry): SatelliteParametersInterface {
        $param = new Parameter($min, $max, $telemetry);

        return $this->add($name, $param);
    }
}