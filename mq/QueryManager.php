<?php

namespace mq;

class QueryManager implements QueryManagerInterface
{
    private $queries = [
        self::SND_QUEUE      => [],
        self::RCV_QUEUE      => [],
        self::TELEMETRY_SND  => [],
        self::SATELLITE_CTRL => [],
    ];

    /**
     * @param string $queue
     * @param mixed $message
     */
    public function put(string $queue, $message): void {
        if (!isset($this->queries[ $queue ])) {
            throw new \RuntimeException("Unknown queue $queue");
        }
        $this->queries[ $queue ][] = $message;
    }

    /**
     * @param string $queue
     * @return mixed
     */
    public function get(string $queue) {
        if (empty($this->queries[ $queue ])) {
            return null;
        }

        return array_shift($this->queries[ $queue ]);
    }
}