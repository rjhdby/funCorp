<?php

namespace program\operations;

class SetPayload implements PayloadInterface
{
    public $variable;
    public $set;

    /**
     * SetPayload constructor.
     * @param string $variable
     * @param mixed $set
     */
    public function __construct(string $variable, $set) {
        $this->variable = $variable;
        $this->set      = $set;
    }
}