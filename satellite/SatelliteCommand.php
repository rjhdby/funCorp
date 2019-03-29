<?php

namespace satellite;
class SatelliteCommand
{
    public $command;
    public $payload;

    /**
     * SatelliteCommand constructor.
     * @param $command
     * @param $payload
     */
    public function __construct($command, $payload = null) {
        $this->command = $command;
        $this->payload = $payload;
    }

}