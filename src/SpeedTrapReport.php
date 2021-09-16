<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener;

use JohnKary\PHPUnit\Listener\SpeedTrapListener;
use PHPUnit\Framework\TestCase;

class SpeedTrapReport {

    /**
     * Collection of slow tests.
     * Keys (string) => Printable label describing the test
     * Values (int) => Test execution time, in milliseconds
     */
    protected $slow = [];


    /**
     * Number of tests to print in slowness report.
     *
     * @var int
     */
    protected $reportLength;

    /**
     * Test execution time (milliseconds) after which a test will be considered
     * "slow" and be included in the slowness report.
     *
     * @var int
     */
    protected $slowThreshold;

    /**
     * @param int $reportLength Number of tests to print in slowness report.
     */
    public function __construct(int $reportLength, int $slowThreshold) {
        $this->reportLength = $reportLength;
        $this->slowThreshold = $slowThreshold;
    }

    /**
     * Whether at least one test has been considered slow.
     */
    public function hasSlowTests(): bool
    {
        return !empty($this->slow);
    }

    /**
     * @return array
     */
    public function getSlow(): array
    {
        arsort($this->slow); // Sort longest running tests to the top
        return $this->slow;
    }

    /**
     * Stores a test as slow.
     */
    public function addSlowTest(TestCase $test, int $time): void
    {
        $this->slow[] = [$test, $time];
    }

    /**
     * Calculate number of slow tests to be hidden from the slowness report
     * due to list length.
     */
    public function getHiddenCount(): int
    {
        $total = count($this->slow);
        $showing = $this->getReportLength();

        $hidden = 0;
        if ($total > $showing) {
            $hidden = $total - $showing;
        }

        return $hidden;
    }

    /**
     * Calculate number of tests to include in slowness report.
     */
    public function getReportLength(): int
    {
        if ($this->reportLength === -1) {
            return count($this->slow);
        }
        return min(count($this->slow), $this->reportLength);
    }


    /**
     * @return int
     */
    public function getSlowThreshold(): int
    {
        return $this->slowThreshold;
    }
}
