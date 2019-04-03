<?php

namespace program\operations;

class BatchSetPayload implements PayloadInterface
{
    public $variables;

    /**
     * GetPayload constructor.
     * @param OperationInterface[] $variables
     */
    public function __construct(array $variables) {
        $this->variables = $variables;
    }
}