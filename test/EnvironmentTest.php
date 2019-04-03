<?php

namespace test;

use environment\Environment;
use environment\EnvironmentInterface;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    private static $testClass = Environment::class;

    /** @var EnvironmentInterface $env */
    private $env;

    /**
     * Ugly test, I know.
     */

    public function testGetFlyProgramLocation() {
        putenv('FLIGHT_PROGRAM=/root/test');
        $this->assertEquals('/root/test', $this->env->getFlyProgramLocation());
        putenv('FLIGHT_PROGRAM');
        $this->expectException(\RuntimeException::class);
        $this->env->getFlyProgramLocation();
    }

    public function testGetExchangeUri() {
        putenv('EXCHANGE_URI=http://localhost');
        $this->assertEquals('http://localhost', $this->env->getExchangeUri());
        putenv('EXCHANGE_URI');
        $this->expectException(\RuntimeException::class);
        $this->env->getExchangeUri();
    }

    public function testGetTelemetryFreq() {
        putenv('TELEMETRY_FREQ=5');
        $this->assertEquals(5, $this->env->getTelemetryFreq());
        putenv('TELEMETRY_FREQ=-5');
        $this->assertEquals(10, $this->env->getTelemetryFreq());
        putenv('TELEMETRY_FREQ=0');
        $this->assertEquals(10, $this->env->getTelemetryFreq());
        putenv('TELEMETRY_FREQ=1');
        $this->assertEquals(1, $this->env->getTelemetryFreq());
        putenv('TELEMETRY_FREQ');
        $this->assertEquals(10, $this->env->getTelemetryFreq());
    }

    protected function setUp(): void {
        $this->env = new self::$testClass();
    }
}
