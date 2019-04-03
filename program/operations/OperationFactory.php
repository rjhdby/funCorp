<?php

namespace program;

class OperationFactory
{
    private const IS_INT           = 1;
    private const IS_UINT          = 2;
    private const IS_UINT_NOT_ZERO = 4;
    private const IS_STRING        = 8;
    private const IS_MIXED         = 16;
    private const IS_BOOL          = 32;
    private const IS_OPTIONAL      = 64;

    private static $setParams = [
        'id'       => self::IS_UINT_NOT_ZERO,
        'deltaT'   => self::IS_UINT,
        'variable' => self::IS_STRING,
        'value'    => self::IS_MIXED,
        'timeout'  => self::IS_UINT_NOT_ZERO,
        'critical' => self::IS_BOOL | self::IS_OPTIONAL
    ];

    public static function makeFromArray(array $data): OperationInterface {
        $type = $data['type'] ?? OperationInterface::SET_PARAMS;
        if (!\defined("OperationInterface::$type")) {
            throw new \RuntimeException("Unknown operation type $type");
        }

        switch ($type) {
            case OperationInterface::SET_PARAMS:
                self::checkKnown($data);
                self::checkRequired($data);

                return new SetOperation($data['id'],
                                        $data['deltaT'],
                                        $data['variable'],
                                        $data['value']);
            case OperationInterface::GET_PARAMS:
        }

        self::checkKnown($data);
        self::checkRequired($data);
    }

    private static function checkKnown(array $input): void {
        foreach ($input as $key => $value) {
            if (!\array_key_exists($key, self::$setParams)) {
                throw new \RuntimeException("Wrong operation's parameter $key");
            }
        }
    }

    private static function checkRequired($input): void {
        foreach (self::$setParams as $value => $type) {
            if (($type & self::IS_OPTIONAL) && !isset($input[ $value ])) {
                continue;
            }
            if (!array_key_exists($value, $input)) {
                throw new \RuntimeException("Operation's parameter required $value");
            }

            switch ($type & (~self::IS_OPTIONAL)) {
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
                case self::IS_BOOL:
                    if (!\is_bool($input[ $value ])) {
                        throw new \RuntimeException("Operation's parameter $value must be bool");
                    }
                    break;
                case self::IS_MIXED:
                    // pass
                    break;
                default:
                    throw new \RuntimeException("Unknown operation's parameter type");
            }
        }
    }
}