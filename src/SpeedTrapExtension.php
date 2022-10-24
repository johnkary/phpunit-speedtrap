<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Runner;
use PHPUnit\TextUI;

final class SpeedTrapExtension implements Runner\Extension\Extension
{
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters
    ): void {
        if (getenv('PHPUNIT_SPEEDTRAP') === 'disabled') {
            return;
        }

        $slowThreshold = 500;

        if ($parameters->has('slowThreshold')) {
            $slowThreshold = (int) $parameters->get('slowThreshold');
        }

        $reportLength = 10;

        if ($parameters->has('reportLength')) {
            $reportLength = (int) $parameters->get('reportLength');
        }

        $speedTrap = new SpeedTrap(
            $slowThreshold,
            $reportLength
        );

        $facade->registerSubscribers(
            new RecordThatTestHasBeenPrepared($speedTrap),
            new RecordThatTestHasPassed($speedTrap),
            new ShowSlowTests($speedTrap),
        );
    }
}
