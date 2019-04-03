<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

class GetOperation extends Operation
{
    /** @var string[] $variables */
    public $variables;

    /**
     * Operation constructor.
     * @param int $deltaT
     * @param array $variables
     */
    public function __construct(int $deltaT, array $variables) {
        parent::__construct(self::GET_PARAM, $deltaT);
        $this->payload = new GetPayload($variables);
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool {
        return true;
    }
}