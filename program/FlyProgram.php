<?php

namespace program;

use satellite\SatelliteParametersInterface;

class FlyProgram
{
    /** @var int $startUp */
    private $startUp;
    /** @var array $operations */
    private $operations;

    private $last;

    /**
     * @param string $json
     * @param SatelliteParametersInterface $params
     */
    public function prepare(string $json, SatelliteParametersInterface $params): void {
        $this->operations = null;
        $this->startUp    = null;
        $this->last       = null;
        $raw              = json_decode($json, true);
        if ($raw === null
            || !isset($raw['startUp'], $raw['operations'])
            || !\is_int($raw['startUp'])
            || !\is_array($raw['operations'])
        ) {
            throw new \RuntimeException('Fly program is invalid');
        }

        if ($raw['startUp'] <= time()) {
            throw new \RuntimeException('Fly program start time is invalid');
        }
        $ids           = [];
        $this->startUp = $raw['startUp'];

        foreach ($raw['operations'] as $data) {
            $op = new Operation($data);
            if (!isset($this->operations[ $op->deltaT ])) {
                $this->operations[ $op->deltaT ] = [];
            }
            $this->operations[ $op->deltaT ][] = $op;
            if (array_key_exists($op->id, $ids)) {
                throw new \RuntimeException('Duplicate operation id: ' . $op->id);
            }
            if (!$params->validate($op->variable, $op->value)) {
                throw new \RuntimeException('Incorrect value: ' . $op->value . ' for parameter ' . $op->variable);
            }
            $ids[ $op->id ] = true;
        }
    }

    /**
     * @return bool
     */
    public function isFinished(): bool {
        return $this->last === max(array_keys($this->operations));
    }

    /**
     * @return Operation[]|null
     */
    public function getNext(): ?array {
        $delta = $this->delta();
        if ($this->last !== $delta && isset($this->operations[ $delta ])) {
            $this->last = $delta;

            return $this->operations[ $delta ];
        }

        return null;
    }

    /**
     * @return int
     */
    public function delta(): int {
        return time() - $this->startUp;
    }

    /**
     * @return int
     */
    public function getStartUp(): int {
        return $this->startUp;
    }
}