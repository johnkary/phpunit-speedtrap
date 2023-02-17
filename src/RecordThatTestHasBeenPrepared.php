<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Event;

final class RecordThatTestHasBeenPrepared implements Event\Test\PreparedSubscriber
{
    public function __construct(private readonly SpeedTrap $speedTrap)
    {
    }

    public function notify(Event\Test\Prepared $event): void
    {
        $this->speedTrap->recordThatTestHasBeenPrepared(
            $event->test(),
            $event->telemetryInfo()->time(),
        );
    }
}
