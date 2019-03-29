<?php

namespace exchanger;

class Response
{
    public $variable;
    public $set;
    public $value;

    /**
     * Response constructor.
     * @param $variable
     * @param $set
     * @param $value
     */
    public function __construct($variable, $set, $value) {
        $this->variable = $variable;
        $this->set      = $set;
        $this->value    = $value;
    }

}