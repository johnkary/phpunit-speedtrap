<?php

declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Event;

final class ShowSlowTests implements Event\TestRunner\ExecutionFinishedSubscriber
{
    public function __construct(private readonly SpeedTrap $speedTrap)
    {
    }

    public function notify(Event\TestRunner\ExecutionFinished $event): void
    {
        $this->speedTrap->showSlowTests();
    }
}
