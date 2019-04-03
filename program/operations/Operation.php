<?php

namespace program\operations;

abstract class Operation implements OperationInterface
{
    protected $type;
    protected $deltaT;
    protected $id;
    protected $critical;
    protected $payload;

    /**
     * Operation constructor.
     * @param string $type
     * @param int $deltaT
     * @param int $id
     * @param bool $critical
     */
    public function __construct($type, $deltaT, $id = -1, $critical = false) {
        $this->type     = $type;
        $this->deltaT   = $deltaT;
        $this->id       = $id;
        $this->critical = $critical;
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
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isCritical(): bool {
        return $this->critical;
    }

    /**
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface {
        return $this->payload;
    }
}