<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

class SetOperation extends Operation
{
    /**
     * Operation constructor.
     * @param int $id
     * @param int $deltaT
     * @param string $variable
     * @param mixed $value
     * @param bool $critical
     */
    public function __construct(int $id, int $deltaT, string $variable, $value, bool $critical) {
        parent::__construct(self::SET_PARAM, $deltaT, $id, $critical);
        $this->payload = new SetPayload($variable, $value);
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool {
        return $data[ $this->payload->variable ]->validate($this->payload->set);
    }
}