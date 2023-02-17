<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Event;

final class PreparedTest
{
    public function __construct(
        private readonly Event\Code\Test $test,
        private readonly Event\Telemetry\HRTime $start
    ) {
    }

    public function test(): Event\Code\Test
    {
        return $this->test;
    }

    public function start(): Event\Telemetry\HRTime
    {
        return $this->start;
    }
}
