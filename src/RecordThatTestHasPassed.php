<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Event;

final class RecordThatTestHasPassed implements Event\Test\PassedSubscriber
{
    public function __construct(private readonly SpeedTrap $speedTrap)
    {
    }

    public function notify(Event\Test\Passed $event): void
    {
        $this->speedTrap->recordThatTestHasPassed(
            $event->test(),
            $event->telemetryInfo()->time(),
        );
    }
}
