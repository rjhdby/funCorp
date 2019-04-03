<?php

namespace test;

use PHPUnit\Framework\TestCase;
use telemetry\TelemetryInterface;

class TelemetryTest extends TestCase
{
    private static $testClass = TelemetryMock::class;

    /** @var TelemetryInterface $telemetry */
    private $telemetry;

    public function testError() {
        $this->telemetry->error('test');
        $result = $this->getActualOutput();
        $this->assertStringMatchesFormat(
            '{"type":"error","timestamp":%d,"message":"test"}',
            $result
        );
    }

    public function testInfo() {
        $this->telemetry->info('test');
        $result = $this->getActualOutput();
        $this->assertStringMatchesFormat(
            '{"time":"%d-%d-%dT%d:%d:%d%s","level":"info","message":"test"}',
            $result
        );
    }

    public function testLog() {
        $this->telemetry->log('test');
        $result = $this->getActualOutput();
        $this->assertStringMatchesFormat(
            '{"time":"%d-%d-%dT%d:%d:%d%s","level":"log","message":"test"}',
            $result
        );
    }

    public function testSendValues() {
        $this->telemetry->sendValues(['one' => 1, 'two' => 2]);
        $result = $this->getActualOutput();
        $this->assertStringMatchesFormat(
            '{"type":"values","timestamp":%d,"message":"one=1&two=2"}',
            $result
        );
    }

    protected function setUp(): void {
        $this->telemetry = new self::$testClass();
    }
}
