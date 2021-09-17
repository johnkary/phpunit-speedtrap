<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener\Renderer;

use JohnKary\PHPUnit\Listener\SpeedTrapReport;

class ConsoleRenderer implements ReportRendererInterface
{
    /** @var SpeedTrapReport */
    protected $speedTrapReport;

    /**
     * @see ReportRendererInterface::__construct
     */
    public function __construct(SpeedTrapReport $speedTrapReport, array $options)
    {
        $this->speedTrapReport = $speedTrapReport;
    }

    /**
     * @see ReportRendererInterface::renderHeader
     */
    public function renderHeader(): void
    {
        echo sprintf(
            "\n\nYou should really speed up these slow tests (>%sms)...\n",
            $this->speedTrapReport->getSlowThreshold()
        );
    }

    /**
     * @see ReportRendererInterface::renderBody
     */
    public function renderBody(): void
    {
        $slowTests = $this->speedTrapReport->getSlow();

        $length = $this->speedTrapReport->getReportLength();
        for ($i = 1; $i <= $length; ++$i) {
            [$testCase, $time] = array_shift($slowTests);
            $label = sprintf('%s::%s', addslashes(get_class($testCase)), $testCase->getName());

            echo sprintf(" %s. %sms to run %s\n", $i, $time, $label);
        }
    }

    /**
     * @see ReportRendererInterface::renderFooter
     */
    public function renderFooter(): void
    {
        if ($hidden = $this->speedTrapReport->getHiddenCount()) {
            printf("...and there %s %s more above your threshold hidden from view\n", $hidden == 1 ? 'is' : 'are', $hidden);
        }
    }
}
