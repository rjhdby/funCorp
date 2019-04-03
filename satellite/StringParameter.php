<?php

namespace satellite;
class IntParameter implements ParameterInterface
{
    private $min;
    private $max;
    private $isImportant;
    private $current;

    /**
     * Parameter constructor.
     * @param int $min
     * @param int $max
     * @param bool $telemetry
     */
    public function __construct(int $min, int $max, $telemetry = true) {
        $this->min         = $min;
        $this->max         = $max;
        $this->isImportant = $telemetry;
        $this->current     = $min;
    }

    /**
     * @return int
     */
    public function get(): int {
        return $this->current;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function set($value): bool {
        $this->current = $value;

        return $this->validate();
    }

    /**
     * @param mixed|null $check
     * @return bool
     */
    public function validate($check = null): bool {
        return ($check ?? $this->current) >= $this->min
               && ($check ?? $this->current) <= $this->max;
    }

    /**
     * @return bool
     */
    public function isTelemetry(): bool {
        return $this->isImportant;
    }
}