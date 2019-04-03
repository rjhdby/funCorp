<?php

namespace program\operations;

class StartOperation implements BaseOperationInterface
{
    /** @var string $type */
    private $type = self::START_PROGRAM;

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
        return 0;
    }
}