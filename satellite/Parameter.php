<?php

namespace satellite;
class Parameter implements ParameterInterface
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

    public function get(): int {
        return $this->current;
    }

    public function set(int $value): bool {
        $this->current = $value;

        return $this->validate();
    }

    public function validate(int $check = null): bool {
        return ($check ?? $this->current) >= $this->min
               && ($check ?? $this->current) <= $this->max;
    }

    public function isTelemetry(): bool {
        return $this->isImportant;
    }
}