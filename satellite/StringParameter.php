<?php
/**
 * Stub class
 */

namespace satellite;
class StringParameter implements ParameterInterface
{
    private $isTelemetry;
    private $current;
    private $newValue;

    /**
     * Parameter constructor.
     * @param bool $telemetry
     */
    public function __construct($telemetry = true) {
        $this->isTelemetry = $telemetry;
    }

    /**
     * @return null|string
     */
    public function get(): ?string {
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
        return $this->current === $this->newValue && $this->current !== null;
    }

    /**
     * @return bool
     */
    public function isTelemetry(): bool {
        return $this->isTelemetry;
    }

    /**
     * @return null|string
     */
    public function getNewValue(): ?string {
        return $this->newValue;
    }

    /**
     * @param $value
     */
    public function setNewValue($value): void {
        $this->newValue = $value;
    }
}