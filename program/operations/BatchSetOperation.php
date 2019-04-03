<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

class BatchSetOperation extends Operation
{
    /**
     * Operation constructor.
     * @param int $deltaT
     * @param OperationInterface[] $operations
     */
    public function __construct(int $deltaT, array $operations) {
        parent::__construct(self::BATCH_SET, $deltaT);
        $this->payload = new BatchSetPayload($operations);
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool {
        return true;
    }
}