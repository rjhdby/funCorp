<?php

namespace program\operations;

class SetOperation implements OperationInterface
{
    /** @var int $id */
    private $id;
    /** @var int $deltaT */
    private $deltaT;
    /** @var string $variable */
    private $variable;
    /** @var mixed $value */
    private $value;

    /** @var string $type */
    private $type = self::SET_PARAM;

    /**
     * Operation constructor.
     * @param int $id
     * @param int $deltaT
     * @param string $variable
     * @param mixed $value
     */
    public function __construct(int $id, int $deltaT, string $variable, $value) {
        $this->id       = $id;
        $this->deltaT   = $deltaT;
        $this->variable = $variable;
        $this->value    = $value;
    }

    /**
     * @return array
     */
    public function getPayload(): array {
        return [$this->variable => $this->value];
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
}