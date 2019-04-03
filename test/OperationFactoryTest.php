<?php

namespace test;

use PHPUnit\Framework\TestCase;
use program\operations\BatchSetOperation;
use program\operations\CheckOperation;
use program\operations\FinishOperation;
use program\operations\GetOperation;
use program\operations\OperationFactory;
use program\operations\SetOperation;
use program\operations\StartOperation;
use program\operations\TelemetryOperation;

class OperationFactoryTest extends TestCase
{
    private $goodSet = ['id'       => 1,
                        'deltaT'   => 1,
                        'variable' => 'a',
                        'value'    => 1,
                        'critical' => true,
                        'timeout'  => 1
    ];
    private $badSet  = [
        ['id'       => 'a',
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1],
        ['id'       => 1,
         'deltaT'   => 'a',
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1],
        ['id'       => 1,
         'deltaT'   => 1,
         'variable' => 1,
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1],
        ['id'       => 0,
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1],
        ['id'       => 1,
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => 'a',
         'timeout'  => 1],
        ['id'       => 1,
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 'a'],
        ['id'       => 1,
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 0
        ],
        ['id'       => 0,
         'deltaT'   => 1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1
        ],
        ['id'       => 1,
         'deltaT'   => -1,
         'variable' => 'a',
         'value'    => 1,
         'critical' => true,
         'timeout'  => 1
        ]
    ];

    public function testMakeTelemetry() {
        $op = OperationFactory::makeTelemetry(1);
        $this->assertInstanceOf(TelemetryOperation::class, $op);
    }

    public function testMakeFinish() {
        $op = OperationFactory::makeFinish(1);
        $this->assertInstanceOf(FinishOperation::class, $op);
    }

    public function testMakeSet() {
        $op = OperationFactory::makeSet($this->goodSet);
        $this->assertInstanceOf(SetOperation::class, $op);
    }

    public function testMakeSetException0() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[0]);
    }

    public function testMakeSetException1() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[1]);
    }

    public function testMakeSetException2() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[2]);
    }

    public function testMakeSetException3() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[3]);
    }

    public function testMakeSetException4() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[4]);
    }

    public function testMakeSetException5() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[5]);
    }

    public function testMakeSetException6() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[6]);
    }

    public function testMakeSetException7() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[7]);
    }

    public function testMakeSetException8() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeSet($this->badSet[8]);
    }

    public function testMakeStart() {
        $op = OperationFactory::makeStart();
        $this->assertInstanceOf(StartOperation::class, $op);
    }

    public function testMakeCheck() {
        $op = OperationFactory::makeCheck($this->goodSet);
        $this->assertInstanceOf(CheckOperation::class, $op);
    }

    public function testMakeCheckException0() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[0]);
    }

    public function testMakeCheckException1() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[1]);
    }

    public function testMakeCheckException2() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[2]);
    }

    public function testMakeCheckException3() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[3]);
    }

    public function testMakeCheckException4() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[4]);
    }

    public function testMakeCheckException5() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[5]);
    }

    public function testMakeCheckException6() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[6]);
    }

    public function testMakeCheckException7() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[7]);
    }

    public function testMakeCheckException8() {
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeCheck($this->badSet[8]);
    }

    public function testMakeBatchSet() {
        $op = OperationFactory::makeBatchSet(1, [new SetOperation(1, 1, 'a', 10, true)]);
        $this->assertInstanceOf(BatchSetOperation::class, $op);
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeGet(1, [1, 'b']);
    }

    public function testMakeGet() {
        $op = OperationFactory::makeGet(1, ['a', 'b']);
        $this->assertInstanceOf(GetOperation::class, $op);
        $this->expectException(\InvalidArgumentException::class);
        OperationFactory::makeGet(1, [1, 'b']);
    }
}
