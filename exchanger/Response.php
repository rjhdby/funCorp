<?php

namespace exchanger;

class Response
{
    public $variable;
    public $set;
    public $value;

    /**
     * Response constructor.
     * @param string $variable
     * @param mixed $set
     * @param mixed $value
     */
    public function __construct(string $variable, $set, $value) {
        $this->variable = $variable;
        $this->set      = $set;
        $this->value    = $value;
    }
}