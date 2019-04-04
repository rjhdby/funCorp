<?php

namespace satellite;
class IntParameter implements ParameterInterface
{
    private $min;
    private $max;
    private $isImportant;
    private $value;
    private $newValue;
    private $speed;

    /**
     * Parameter constructor.
     * @param int $min
     * @param int $max
     * @param $speed
     * @param bool $telemetry
     */
    public function __construct(int $min, int $max, $speed, $telemetry = true) {
        $this->min         = $min;
        $this->max         = $max;
        $this->isImportant = $telemetry;
        $this->value       = $min;
        $this->speed       = $speed;
    }

    /**
     * @return int
     */
    public function get(): ?int {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function set($value): bool {
        $this->value = $value;

        return $this->validate();
    }

    /**
     * @param mixed|null $check
     * @return bool
     */
    public function validate($check = null): bool {
        return ($check ?? $this->value) >= $this->min
               && ($check ?? $this->value) <= $this->max;
    }

    /**
     * @return bool
     */
    public function isTelemetry(): bool {
        return $this->isImportant;
    }

    /**
     * @return int
     */
    public function getNewValue(): ?int {
        return $this->newValue;
    }

    /**
     * @param $value
     */
    public function setNewValue($value): void {
        $this->newValue = $value;
    }

    public function getSpeed(): int {
        return $this->speed;
    }
}