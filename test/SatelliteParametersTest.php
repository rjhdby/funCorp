<?php

namespace test;

use PHPUnit\Framework\TestCase;
use satellite\IntParameter;
use satellite\SatelliteParameters;
use satellite\SatelliteParametersInterface;

class SatelliteParametersTest extends TestCase
{
    private static $testClass      = SatelliteParameters::class;
    private static $parameterClass = IntParameter::class;

    /** @var SatelliteParametersInterface $params */
    private $params;

    public function testGet() {
        $this->assertFalse($this->params['second']->isTelemetry());
    }

    public function testGetException() {
        $this->assertNull($this->params['unknown']);
    }

    public function testGetNames() {
        $this->assertEquals(['first', 'second'], $this->params->getNames());
    }

    protected function setUp(): void {
        $this->params = new self::$testClass();
        $this->params->add('first', new self::$parameterClass(0, 10, 0, true));
        $this->params->add('second', new self::$parameterClass(-10, 10, 0, false));
    }
}
