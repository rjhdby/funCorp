<?php

namespace test;

use environment\Environment;
use environment\EnvironmentInterface;
use PHPUnit\Framework\TestCase;
use program\FlyProgram;
use program\FlyProgramFactory;
use satellite\SatelliteParameters;
use satellite\SatelliteParametersFactory;

class FlyProgramFactoryTest extends TestCase
{
    private const GOOD = <<<EOL
{
  "startUp": "time",
  "operations": [
    {
      "id": 2,
      "deltaT": 7,
      "variable": "radioPowerDbm",
      "value": 50,
      "timeout": 1
    }
  ]
}
EOL;

    private const BAD_1 = <<<EOL
{
  "startUp": "time",
  "operations": [
    {
      "id": 2,
      "deltaT": 7,
      "variable": "radioPowerDbm",
      "value": -50,
      "timeout": 1
    }
  ]
}
EOL;

    private const BAD_2 = <<<EOL
{
  "startUp": "time",
  "operations": [
    {
      "id": 1,
      "deltaT": 0,
      "variable": "coolingSystemPowerPct",
      "value": 30,
      "timeout": 1
    },
    {
      "id": 1,
      "deltaT": 7,
      "variable": "radioPowerDbm",
      "value": -50,
      "timeout": 1
    }
  ]
}
EOL;

    private const BAD_3 = <<<EOL
aaa{
  "startUp": "time",
  "operations": [
    {
      "id": 2,
      "deltaT": 7,
      "variable": "radioPowerDbm",
      "value": 10,
      "timeout": 1
    }
  ]
}
EOL;

    private $params;
    /** @var EnvironmentInterface $env */
    private $env;

    public function testWrongSetValueForParameter() {
        $this->expectException(\InvalidArgumentException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            $this->prepareJson(self::BAD_1),
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testWrongStartTime() {
        $this->expectException(\InvalidArgumentException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            $this->prepareJson(self::GOOD, -10),
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testDuplicateId() {
        $this->expectException(\InvalidArgumentException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            $this->prepareJson(self::BAD_2),
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testNoOperations() {
        $this->expectException(\InvalidArgumentException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            '{"startUp": ' . (time() + 3) . '}',
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testNoStartTime() {
        $this->expectException(\InvalidArgumentException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            '{"operations": []}',
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testFileNotFound() {
        $this->expectException(\RuntimeException::class);
        FlyProgramFactory::getFromFile(
            FlyProgram::class,
            'noFile',
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    public function testMalformedJson() {
        $this->expectException(\RuntimeException::class);
        FlyProgramFactory::getFromJson(
            FlyProgram::class,
            $this->prepareJson(self::BAD_3),
            $this->params,
            $this->env->getTelemetryFreq()
        );
    }

    protected function setUp(): void {
        $this->params = SatelliteParametersFactory::createFromJson(SatelliteParameters::class, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'parameters.json'));
        $this->env    = new Environment();
    }

    private function prepareJson($json, $delay = 3) {
        $time = time() + $delay;

        return str_replace('"time"', $time, $json);
    }
}
