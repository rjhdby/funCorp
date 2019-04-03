<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

class CheckOperation extends Operation
{
    /**
     * Operation constructor.
     * @param int $id
     * @param int $deltaT
     * @param string $variable
     * @param int $set
     * @param bool $critical
     */
    public function __construct(int $id, int $deltaT, string $variable, int $set, bool $critical) {
        parent::__construct(self::CHECK_PARAM, $deltaT, $id, $critical);
        $this->payload = new SetPayload($variable, $set);
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool {
        return true; //todo
    }
}