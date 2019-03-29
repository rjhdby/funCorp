<?php

namespace exchanger;

use mq\QueryManagerInterface;

interface ExchangeInterface
{
    /**
     * @param QueryManagerInterface $mq
     * @param string $sndQueue
     * @param string $rcvQueue
     */
    public function initialize(QueryManagerInterface $mq,
                               string $sndQueue,
                               string $rcvQueue): void;

    public function processTasks(): void;
}