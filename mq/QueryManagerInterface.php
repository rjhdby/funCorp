<?php

namespace mq;

interface QueryManagerInterface
{
    public const SND_QUEUE      = 'SND_QUEUE';
    public const RCV_QUEUE      = 'RCV_QUEUE';
    public const TELEMETRY_SND  = 'TELEMETRY_SND';
    public const SATELLITE_CTRL = 'SATELLITE_CTRL';

    public function put(string $queue, $message): void;

    public function get(string $queue);
}