<?php

namespace test;

use PHPUnit\Framework\TestCase;
use satellite\IntParameter;
use satellite\ParameterInterface;

class ParameterTest extends TestCase
{
    /** @var ParameterInterface $param */
    private $param;

    private static $testClass = IntParameter::class;

    private const MIN     = -10;
    private const MAX     = 10;
    private const BIGGER  = 15;
    private const SMALLER = 15;
    private const VALID   = 5;

    public function testValidate() {
        $this->assertTrue($this->param->validate());

        $this->param->set(self::MIN);
        $this->assertTrue($this->param->validate());

        $this->param->set(self::MAX);
        $this->assertTrue($this->param->validate());

        $this->param->set(self::BIGGER);
        $this->assertFalse($this->param->validate());

        $this->param->set(self::SMALLER);
        $this->assertFalse($this->param->validate());

        $this->assertTrue($this->param->validate(self::VALID));
        $this->assertTrue($this->param->validate(self::MIN));
        $this->assertTrue($this->param->validate(self::MAX));
        $this->assertFalse($this->param->validate(self::BIGGER));
        $this->assertFalse($this->param->validate(self::SMALLER));
    }

    public function testSet() {
        $result = $this->param->set(self::BIGGER);
        $this->assertSame(self::BIGGER, $this->param->get());
        $this->assertFalse($result);

        $result = $this->param->set(self::SMALLER);
        $this->assertSame(self::SMALLER, $this->param->get());
        $this->assertFalse($result);

        $result = $this->param->set(self::VALID);
        $this->assertSame(self::VALID, $this->param->get());
        $this->assertTrue($result);

        $result = $this->param->set(self::MIN);
        $this->assertSame(self::MIN, $this->param->get());
        $this->assertTrue($result);

        $result = $this->param->set(self::MAX);
        $this->assertSame(self::MAX, $this->param->get());
        $this->assertTrue($result);
    }

    protected function setUp(): void {
        $this->param = new self::$testClass(self::MIN, self::MAX);
        $this->param->set(self::VALID);
    }
}
