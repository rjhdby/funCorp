<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

interface OperationInterface extends BaseOperationInterface
{
    /**
     * @return mixed
     */
    public function getPayload();

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data): bool;

    /**
     * @return bool
     */
    public function isCritical(): bool;
}