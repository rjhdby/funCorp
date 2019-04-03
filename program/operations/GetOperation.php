<?php

namespace program\operations;

class GetOperation implements OperationInterface
{
    /** @var int $deltaT */
    private $deltaT;
    /** @var string[] $variables */
    private $variables;

    /** @var string $type */
    private $type = self::GET_PARAM;

    /**
     * Operation constructor.
     * @param int $deltaT
     * @param array $variables
     */
    public function __construct(int $deltaT, array $variables) {
        $this->deltaT    = $deltaT;
        $this->variables = $variables;
    }

    /**
     * @return array
     */
    public function getPayload(): array {
        return $this->variables;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return -1;
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