<?php

namespace program\operations;

class OperationFactory
{
    private const IS_INT           = 1;
    private const IS_UINT          = 2;
    private const IS_UINT_NOT_ZERO = 4;
    private const IS_STRING        = 8;
    private const IS_MIXED         = 16;
    private const IS_BOOL          = 32;
    private const IS_ARRAY         = 64;

    private const IS_OPTIONAL = 256;

    private static $setParams = [
        'id'       => self::IS_UINT_NOT_ZERO,
        'deltaT'   => self::IS_UINT,
        'variable' => self::IS_STRING,
        'value'    => self::IS_MIXED,
        'timeout'  => self::IS_UINT_NOT_ZERO,
        'critical' => self::IS_BOOL | self::IS_OPTIONAL
    ];

    /**
     * @return OperationInterface
     */
    public static function makeStart(): OperationInterface {
        return self::makeFromArray(['type' => OperationInterface::START_PROGRAM]);
    }

    /**
     * @param int $delta
     * @return OperationInterface
     */
    public static function makeFinish(int $delta): OperationInterface {
        return self::makeFromArray(['type' => OperationInterface::FINISH_PROGRAM, 'deltaT' => $delta]);
    }

    /**
     * @param array $data
     * @return OperationInterface
     */
    public static function makeCheck(array $data): OperationInterface {
        $data['type'] = OperationInterface::CHECK_PARAM;

        return self::makeFromArray($data);
    }

    /**
     * @param array $data
     * @return OperationInterface
     */
    public static function makeSet(array $data): OperationInterface {
        $data['type'] = OperationInterface::SET_PARAM;

        return self::makeFromArray($data);
    }

    /**
     * @param int $delta
     * @param array $values
     * @return OperationInterface
     */
    public static function makeBatchSet(int $delta, array $values): OperationInterface {
        $data['type']   = OperationInterface::BATCH_SET;
        $data['deltaT'] = $delta;
        $data['values'] = $values;

        return self::makeFromArray($data);
    }

    /**
     * @param int $delta
     * @param array $values
     * @return OperationInterface
     */
    public static function makeGet(int $delta, array $values): OperationInterface {
        $data['type']   = OperationInterface::GET_PARAM;
        $data['deltaT'] = $delta;
        $data['values'] = $values;

        return self::makeFromArray($data);
    }

    /**
     * @param int $delta
     * @return OperationInterface
     */
    public static function makeTelemetry(int $delta): OperationInterface {
        $data['type']   = OperationInterface::SEND_TELEMETRY;
        $data['deltaT'] = $delta;

        return self::makeFromArray($data);
    }

    private static function makeFromArray(array $data): OperationInterface {
        switch ($data['type']) {
            case OperationInterface::SET_PARAM:
                self::checkPrerequisites($data, self::$setParams);

                return new SetOperation($data['id'],
                                        $data['deltaT'],
                                        $data['variable'],
                                        $data['value'],
                                        $data['critical'] ?? true
                );
            case OperationInterface::CHECK_PARAM:
                self::checkPrerequisites($data, self::$setParams);

                return new CheckOperation($data['id'],
                                          $data['deltaT'] + $data['timeout'],
                                          $data['variable'],
                                          $data['value'],
                                          $data['critical'] ?? true
                );
            case OperationInterface::GET_PARAM:
                self::checkPrerequisites($data, ['deltaT' => self::IS_UINT, 'values' => self::IS_ARRAY]);
                foreach ($data['values'] as $line) {
                    if (!\is_string($line)) {
                        throw new \InvalidArgumentException("Operation's parameter 'values' must string array");
                    }
                }

                return new GetOperation($data['deltaT'], $data['values']);
            case OperationInterface::START_PROGRAM:
                return new StartOperation();
            case OperationInterface::FINISH_PROGRAM:
                self::checkPrerequisites($data, ['deltaT' => self::IS_UINT]);

                return new FinishOperation($data['deltaT']);
            case OperationInterface::BATCH_SET:
                self::checkPrerequisites($data, ['deltaT' => self::IS_UINT, 'values' => self::IS_ARRAY]);
                foreach ($data['values'] as $op) {
                    if (!$op instanceof SetOperation) {
                        throw new \InvalidArgumentException("Operation's parameter 'value' must SetOperation array");
                    }
                }

                return new BatchSetOperation($data['deltaT'], $data['values']);
            case OperationInterface::SEND_TELEMETRY:
                self::checkPrerequisites($data, ['deltaT' => self::IS_UINT]);

                return new TelemetryOperation($data['deltaT']);
            default:
                throw new \InvalidArgumentException('Unknown operation type: ' . $data['type']);
        }
    }

    private static function checkPrerequisites($input, $params): void {
        foreach ($input as $key => $value) {
            if (!\array_key_exists($key, $params) && $key !== 'type') {
                throw new \InvalidArgumentException("Wrong operation's parameter $key");
            }
        }
        foreach ($params as $value => $type) {
            if (($type & self::IS_OPTIONAL) && !isset($input[ $value ])) {
                continue;
            }
            if (!array_key_exists($value, $input)) {
                throw new \InvalidArgumentException("Operation's parameter required $value");
            }

            switch ($type & (~self::IS_OPTIONAL)) {
                case self::IS_INT:
                    if (!\is_int($input[ $value ])) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be integer");
                    }
                    break;
                case self::IS_UINT:
                    if (!\is_int($input[ $value ]) || $input[ $value ] < 0) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be integer >=0");
                    }
                    break;
                case self::IS_UINT_NOT_ZERO:
                    if (!\is_int($input[ $value ]) || $input[ $value ] <= 0) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be integer >0");
                    }
                    break;
                case self::IS_STRING:
                    if (!\is_string($input[ $value ])) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be string");
                    }
                    break;
                case self::IS_BOOL:
                    if (!\is_bool($input[ $value ])) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be bool");
                    }
                    break;
                case self::IS_ARRAY:
                    if (!\is_array($input[ $value ])) {
                        throw new \InvalidArgumentException("Operation's parameter $value must be array");
                    }
                    break;
                case self::IS_MIXED:
                    // pass
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown operation's parameter type");
            }
        }
    }
}