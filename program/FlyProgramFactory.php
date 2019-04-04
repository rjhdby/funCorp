<?php

namespace program;

use program\operations\GetPayload;
use program\operations\OperationFactory;
use program\operations\OperationInterface;
use program\operations\SetOperation;
use program\operations\SetPayload;
use satellite\SatelliteParametersInterface;

class FlyProgramFactory
{
    /** @var SatelliteParametersInterface $params */
    private static $params;
    /** @var int $telemetryFqr */
    private static $telemetryFqr;

    /** @var int $last */
    private static $last;

    /**
     * @param string $class // FlyProgramInterface class name
     * @param string $file
     * @param SatelliteParametersInterface $params
     * @param int $telemetryFrq
     * @return FlyProgramInterface
     */
    public static function getFromFile(string $class, string $file, SatelliteParametersInterface $params, int $telemetryFrq): FlyProgramInterface {
        if (!is_file($file)) {
            throw new \RuntimeException('Fly program file is not accessible');
        }
        $json = file_get_contents($file);

        return self::getFromJson($class, $json, $params, $telemetryFrq);
    }

    /**
     * @param string $class // FlyProgramInterface class name
     * @param string $json
     * @param SatelliteParametersInterface $params
     * @param int $telemetryFrq
     * @return FlyProgramInterface
     */
    public static function getFromJson(string $class, string $json, SatelliteParametersInterface $params, int $telemetryFrq): FlyProgramInterface {
        $raw = json_decode($json, true);
        if ($raw === null) {
            throw new \RuntimeException('Fly program json is invalid');
        }

        self::$params       = $params;
        self::$telemetryFqr = $telemetryFrq;
        $startUp            = self::getStartUp($raw);
        $operations         = self::compileStepOne($raw);

        /** @var FlyProgramInterface $fly */
        $fly = new $class($startUp, $operations);
        if (!$fly instanceof FlyProgramInterface) {
            throw new \InvalidArgumentException("Wrong fly program class provided: $class instead of FlyProgramInterface");
        }

        return $fly;
    }

    /**
     * @param array $input
     * @return array
     */
    private static function compileStepOne(array $input): array {
        if (!(isset($input['operations']) && \is_array($input['operations']))) {
            throw new \InvalidArgumentException('Fly program is invalid: operations');
        }
        $ids        = [];
        $operations = [];

        foreach ($input['operations'] as $data) {
            /** @var SetOperation $op */
            $op = OperationFactory::makeSet($data);
            if (array_key_exists($op->getId(), $ids)) {
                throw new \InvalidArgumentException('Duplicate operation id: ' . $op->getId());
            }

            if (!$op->validate(self::$params)) {
                throw new \InvalidArgumentException('Incorrect "set" operation: ' . json_encode($op->getPayload(), true));
            }
            $ids[ $op->getId() ]             = true;
            $operations[ $op->getDelta() ][] = $op;
            $opCheck                         = OperationFactory::makeCheck($data);
            $operations[ $opCheck->getDelta() ][] = $opCheck;
        }

        self::$last = max(array_keys($operations));

        self::compileStepTwo($operations);

        return $operations;
    }

    /**
     * @param array $operations
     * @return void
     */
    private static function compileStepTwo(array &$operations): void {
        self::addTelemetry($operations);
        foreach ($operations as $delta => $batch) {
            $operations[ $delta ] = self::pack($delta, $batch);
        }
        if (!isset($operations[0])) {
            $operations[0] = [];
        }
        array_unshift($operations[0], OperationFactory::makeStart());
        $operations[ self::$last ][] = OperationFactory::makeFinish(self::$last);
    }

    /**
     * @param OperationInterface[] $operations
     */
    private static function addTelemetry(array &$operations): void {
        $finish    = max(array_keys($operations));
        $telemetry = [];
        foreach (self::$params->getNames() as $name) {
            if (self::$params[ $name ]->isTelemetry()) {
                $telemetry[] = $name;
            }
        }

        for ($delta = 0; $delta <= $finish; $delta += self::$telemetryFqr) {
            $operations[ $delta ][] = OperationFactory::makeGet($delta, $telemetry);
            $operations[ $delta ][] = OperationFactory::makeTelemetry($delta);
        }
    }

    /**
     * @param int $delta
     * @param OperationInterface[] $batch
     * @return OperationInterface[]
     */
    private static function pack(int $delta, array $batch): array {
        $set       = [];
        $setParams = [];
        $check     = [];
        $get       = [];
        $others    = [];

        foreach ($batch as $op) {
            switch ($op->getType()) {
                case OperationInterface::SET_PARAM:
                    $set[] = $op;
                    /** @var SetPayload $payload */
                    $payload     = $op->getPayload();
                    $setParams[] = $payload->variable;
                    break;
                case OperationInterface::GET_PARAM:
                    /** @var GetPayload $payload */
                    $payload = $op->getPayload();
                    array_push($get, ...$payload->variables);
                    break;
                case OperationInterface::CHECK_PARAM:
                    $check[] = $op;
                    /** @var SetPayload $payload */
                    $payload = $op->getPayload();
                    $get[]   = $payload->variable;
                    break;
                default:
                    $others[] = $op;
            }
        }
        $out = [];
        $get = array_diff(array_unique($get), $setParams);
        if (!empty($get)) {
            $out[] = OperationFactory::makeGet($delta, $get);
        }
        if (!empty($set)) {
            $out[] = OperationFactory::makeBatchSet($delta, $set);
        }
        if (!empty($check)) {
            array_push($out, ...$check);
        }
        if (!empty($others)) {
            array_push($out, ...$others);
        }

        return $out;
    }

    private static function getStartUp(array $input): int {
        if (!(isset($input['startUp']) && \is_int($input['startUp']))) {
            throw new \InvalidArgumentException('Fly program is invalid: startUp');
        }
        if ($input['startUp'] <= time()) {
            throw new \InvalidArgumentException('Fly program start time is invalid. Start time is less than the current');
        }

        return $input['startUp'];
    }
}