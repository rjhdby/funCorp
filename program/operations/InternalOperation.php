<?php

namespace program\operations;

use satellite\SatelliteParametersInterface;

abstract class InternalOperation extends Operation
{
    /**
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface {
        return new class() implements PayloadInterface
        {
        };
    }

    /**
     * @return int
     */
    public function getId(): int {
        return -1;
    }

    /**
     * @param SatelliteParametersInterface $data
     * @return bool
     */
    public function validate(SatelliteParametersInterface $data = null): bool {
        return true;
    }

    /**
     * @return bool
     */
    public function isCritical(): bool {
        return false;
    }
}