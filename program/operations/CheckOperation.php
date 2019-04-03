<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

class CheckOperation implements OperationInterface
{
    /** @var int $id */
    private $id;
    /** @var int $deltaT */
    private $deltaT;
    /** @var string $variable */
    private $variable;
    /** @var mixed $value */
    private $value;
    /** @var bool $critical */
    private $critical;

    /** @var string $type */
    private $type = self::CHECK_PARAM;

    /**
     * Operation constructor.
     * @param int $id
     * @param int $deltaT
     * @param string $variable
     * @param $value
     * @param bool $critical
     */
    public function __construct(int $id, int $deltaT, string $variable, $value, bool $critical) {
        $this->id       = $id;
        $this->deltaT   = $deltaT;
        $this->variable = $variable;
        $this->value    = $value;
        $this->critical = $critical;
    }

    /**
     * @return array
     */
    public function getPayload(): array {
        return [$this->variable => ['value' => $this->value, 'critical' => $this->critical]];
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getDelta(): int {
        return $this->deltaT;
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool {
        return true;
    }

    /**
     * @return bool
     */
    public function isCritical(): bool {
        return $this->critical;
    }
}