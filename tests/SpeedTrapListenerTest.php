<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener\Tests;

use JohnKary\PHPUnit\Listener\SpeedTrapListener;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;

final class SpeedTrapListenerTest extends TestCase
{
    /** @var TestListener  */
    private $speedTrapListener;

    public function setUp(): void
    {
        parent::setUp();

        $this->speedTrapListener = new SpeedTrapListener(['slowThreshold' => 444]);
    }

    public function testItPrintsSpeedData(): void
    {
        $testSuite = new TestSuite();
        $this->speedTrapListener->startTestSuite($testSuite);
        $this->speedTrapListener->startTest($this);

        $this->speedTrapListener->endTest($this, 1 /*second*/);

        $output = $this->captureOutput(function () use ($testSuite) {
            $this->speedTrapListener->endTestSuite($testSuite);
        });

        self::assertSame(
            '

You should really speed up these slow tests (>444ms)...
 1. 1000ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::testItPrintsSpeedData

 Fast tests: 0.0 seconds (0.00%)
 Slow tests: 1.0 seconds (100.00%)
'
            ,
            $output
        );
    }


    public function testItDisplaysTotalSlowTestTime(): void
    {
        $testSuite = new TestSuite();

        $test1 = new self('test one');
        $test2 = new self('test two');

        $this->speedTrapListener->startTestSuite($testSuite);
        $this->speedTrapListener->startTest($test1);
        $this->speedTrapListener->endTest($test1, 2);

        $this->speedTrapListener->startTest($test2);
        $this->speedTrapListener->endTest($test2, 1.5);

        $output = $this->captureOutput(function () use ($testSuite) {
            $this->speedTrapListener->endTestSuite($testSuite);
        });

        self::assertSame(
            '

You should really speed up these slow tests (>444ms)...
 1. 2000ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::test one
 2. 1500ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::test two

 Fast tests: 0.0 seconds (0.00%)
 Slow tests: 3.5 seconds (100.00%)
'
            ,
            $output
        );
    }

    public function testItDisplaysTotalSlowAndFastTestTime(): void
    {
        $testSuite = new TestSuite();

        $test1 = new self('slow test one');
        $test2 = new self('slow test two');

        $fastTest = new self('this one is fast');

        $this->speedTrapListener->startTestSuite($testSuite);
        $this->speedTrapListener->startTest($test1);
        $this->speedTrapListener->endTest($test1, 2 /* duration in seconds */);


        $this->speedTrapListener->startTest($test2);
        $this->speedTrapListener->endTest($test2, 1.5);

        $this->speedTrapListener->startTest($fastTest);
        $this->speedTrapListener->endTest($fastTest, 0.4);

        $output = $this->captureOutput(function () use ($testSuite) {
            $this->speedTrapListener->endTestSuite($testSuite);
        });

        self::assertSame(
            '

You should really speed up these slow tests (>444ms)...
 1. 2000ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::slow test one
 2. 1500ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::slow test two

 Fast tests: 0.4 seconds (10.26%)
 Slow tests: 3.5 seconds (89.74%)
'
            ,
            $output
        );

        self::assertEqualsWithDelta(10.26, 100*0.4/(0.4+3.5), 0.01);
        self::assertEqualsWithDelta(89.74, 100*3.5/(0.4+3.5), 0.01);
    }

    private function captureOutput(\Closure $closure): string
    {
        ob_start();
        $closure();
        return ob_get_clean();
    }
}