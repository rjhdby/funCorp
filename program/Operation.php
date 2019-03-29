<?php

namespace program;

class Operation
{
    private const IS_INT           = 0;
    private const IS_UINT          = 1;
    private const IS_UINT_NOT_ZERO = 2;
    private const IS_STRING        = 4;
    public $id;
    public $deltaT;
    public $variable;
    public $value;
    public $timeout;
    public $critical;

    private static $known    = ['id', 'deltaT', 'variable', 'value', 'timeout', 'critical'];
    private static $required = [
        'id'       => self::IS_UINT_NOT_ZERO,
        'deltaT'   => self::IS_UINT,
        'variable' => self::IS_STRING,
        'value'    => self::IS_INT,
        'timeout'  => self::IS_UINT_NOT_ZERO
    ];

    /**
     * Operation constructor.
     * @param array $input
     */
    public function __construct(array $input) {
        $this->checkKnown($input);
        $this->checkRequired($input);

        $this->id       = $input['id'];
        $this->deltaT   = $input['deltaT'];
        $this->variable = $input['variable'];
        $this->value    = $input['value'];
        $this->timeout  = $input['timeout'];
        $this->critical = $input['critical'] ?? true;
    }

    private function checkKnown(array $input): void {
        foreach ($input as $key => $value) {
            if (!\in_array($key, self::$known, true)) {
                throw new \RuntimeException("Wrong operation's parameter $key");
            }
        }
    }

    private function checkRequired($input): void {
        foreach (self::$required as $value => $type) {
            if (!array_key_exists($value, $input)) {
                throw new \RuntimeException("Operation's parameter required $value");
            }
            switch ($type) {
                case self::IS_INT:
                    if (!\is_int($input[ $value ])) {
                        throw new \RuntimeException("Operation's parameter $value must be integer");
                    }
                    break;
                case self::IS_UINT:
                    if (!\is_int($input[ $value ]) || $input[ $value ] < 0) {
                        throw new \RuntimeException("Operation's parameter $value must be integer >=0");
                    }
                    break;
                case self::IS_UINT_NOT_ZERO:
                    if (!\is_int($input[ $value ]) || $input[ $value ] <= 0) {
                        throw new \RuntimeException("Operation's parameter $value must be integer >0");
                    }
                    break;
                case self::IS_STRING:
                    if (!\is_string($input[ $value ])) {
                        throw new \RuntimeException("Operation's parameter $value must be string");
                    }
                    break;
                default:
                    throw new \RuntimeException("Unknown operation's parameter type");
            }
        }
    }
}