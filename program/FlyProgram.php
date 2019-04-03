<?php

namespace program;

use program\operations\InternalOperation;

class FlyProgram implements FlyProgramInterface
{
    /** @var int $startUp */
    private $startUp;
    private $finish;
    /** @var InternalOperation[] $operations */
    private $operations;

    private $last;

    public function __construct(int $startUp, array $operations) {
        $this->startUp    = $startUp;
        $this->operations = $operations;
        $this->finish     = max(array_keys($operations));
    }

    /**
     * @return bool
     */
    public function isFinished(): bool {
        return $this->getDelta() >= $this->finish;
    }

    /**
     * @return InternalOperation[]|null
     */
    public function getNext(): ?array {
        $delta = $this->getDelta();
        if ($this->last !== $delta && isset($this->operations[ $delta ])) {
            $this->last = $delta;

            return $this->operations[ $delta ];
        }

        return null;
    }

    /**
     * @return int
     */
    public function getDelta(): int {
        return time() - $this->startUp;
    }

    /**
     * @return int
     */
    public function getStartUp(): int {
        return $this->startUp;
    }
}