<?php

namespace exchanger;

class InvalidResponse extends Response
{

    /**
     * InvalidResponse constructor.
     * @param string $text
     */
    public function __construct(string $text) {
        parent::__construct($text, 0, 0);
    }
}