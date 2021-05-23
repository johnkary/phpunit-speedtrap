<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener\Tests;

use JohnKary\PHPUnit\Listener\SpeedTrapListener;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Util\Test;

final class SpeedTrapListenerTest extends TestCase
{
    /** @var TestListener  */
    private $speedTrapListener;

    public function setUp(): void
    {
        parent::setUp();

        $this->speedTrapListener = new SpeedTrapListener([]);
    }

    public function testItPrintsSpeedData(): void
    {
        $testSuite = new TestSuite();
        $this->speedTrapListener->startTestSuite($testSuite);
        $this->speedTrapListener->startTest($this);

        $oneSecond = 1;
        $this->speedTrapListener->endTest($this, $oneSecond);

        $output = $this->captureOutput(function () use ($testSuite) {
            $this->speedTrapListener->endTestSuite($testSuite);
        });

        self::assertSame(
            '

You should really speed up these slow tests (>500ms)...
 1. 1000ms to run JohnKary\\\\PHPUnit\\\\Listener\\\\Tests\\\\SpeedTrapListenerTest::testItPrintsSpeedData
'
            ,
            $output
        );
    }

    private function captureOutput(\Closure $closure): string
    {
        ob_start();
        $closure();
        return ob_get_clean();
    }
}