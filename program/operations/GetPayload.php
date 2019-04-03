<?php

namespace program\operations;

class GetPayload implements PayloadInterface
{
    public $variables;

    /**
     * GetPayload constructor.
     * @param string[] $variables
     */
    public function __construct(array $variables) {
        $this->variables = $variables;
    }
}