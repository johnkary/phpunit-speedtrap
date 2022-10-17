<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Extension;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Util\Test as TestUtil;

/**
 * A PHPUnit Extension that exposes your slowest running tests by outputting
 * results directly to the console.
 */
class SpeedTrap implements AfterSuccessfulTestHook, BeforeFirstTestHook, AfterLastTestHook
{
    /**
     * Slowness profiling enabled by default. Set to false to disable profiling
     * and reporting.
     *
     * Use environment variable "PHPUNIT_SPEEDTRAP" set to value "disabled" to
     * disable profiling.
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     */
    protected $suites = 0;

    /**
     * Test execution time (milliseconds) after which a test will be considered
     * "slow" and be included in the slowness report.
     *
     * @var int
     */
    protected $slowThreshold;

    /**
     * Number of tests to print in slowness report.
     *
     * @var int
     */
    protected $reportLength;

    /**
     * Collection of slow tests.
     * Keys (string) => Printable label describing the test
     * Values (int) => Test execution time, in milliseconds
     */
    protected $slow = [];

    public function __construct(array $options = [])
    {
        $this->enabled = getenv('PHPUNIT_SPEEDTRAP') === 'disabled' ? false : true;

        $this->loadOptions($options);
    }

    /**
     * A test successfully ended.
     *
     * @param string $test
     * @param float  $time
     */
    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        if (!$this->enabled) return;

        $timeMS = $this->toMilliseconds($time);
        $threshold = $this->getSlowThreshold($test);

        if ($this->isSlow($timeMS, $threshold)) {
            $this->addSlowTest($test, $timeMS);
        }
    }

    /**
     * A test suite started.
     */
    public function executeBeforeFirstTest(): void
    {
        if (!$this->enabled) return;

        $this->suites++;
    }

    /**
     * A test suite ended.
     */
    public function executeAfterLastTest(): void
    {
        if (!$this->enabled) return;

        $this->suites--;

        if (0 === $this->suites && $this->hasSlowTests()) {
            arsort($this->slow); // Sort longest running tests to the top

            $this->renderHeader();
            $this->renderBody();
            $this->renderFooter();
        }
    }

    /**
     * Whether the given test execution time is considered slow.
     *
     * @param int $time          Test execution time in milliseconds
     * @param int $slowThreshold Test execution time at which a test should be considered slow, in milliseconds
     */
    protected function isSlow(int $time, int $slowThreshold): bool
    {
        return $slowThreshold && $time >= $slowThreshold;
    }

    /**
     * Stores a test as slow.
     *
     * @param int $time Test execution time that was considered slow, in milliseconds
     */
    protected function addSlowTest(string $test, int $time): void
    {
        $label = $this->makeLabel($test);

        $this->slow[$label] = $time;
    }

    /**
     * Whether at least one test has been considered slow.
     */
    protected function hasSlowTests(): bool
    {
        return !empty($this->slow);
    }

    /**
     * Convert PHPUnit's reported test time (microseconds) to milliseconds.
     */
    protected function toMilliseconds(float $time): int
    {
        return (int) round($time * 1000);
    }

    /**
     * Label describing a slow test case. Formatted to support copy/paste with
     * PHPUnit's --filter CLI option:
     *
     *     vendor/bin/phpunit --filter 'JohnKary\\PHPUnit\\Extension\\Tests\\SomeSlowTest::testWithDataProvider with data set "Rock"'
     */
    protected function makeLabel(string $test): string
    {
        list($class, $testName) = explode('::', $test);

        // Remove argument list from end of string that is appended
        // by default \PHPUnit\Framework\TestCase->toString() so slowness report
        // output compatible with phpunit --filter flag
        $testName = preg_replace('/\s\(.*\)$/', '', $testName);

        return sprintf('%s::%s', addslashes($class), $testName);
    }

    /**
     * Calculate number of tests to include in slowness report.
     */
    protected function getReportLength(): int
    {
        return min(count($this->slow), $this->reportLength);
    }

    /**
     * Calculate number of slow tests to be hidden from the slowness report
     * due to list length.
     */
    protected function getHiddenCount(): int
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
     * Renders slowness report header.
     */
    protected function renderHeader(): void
    {
        echo sprintf("\n\nThe following tests were detected as slow (>%sms)\n", $this->slowThreshold);
    }

    /**
     * Renders slowness report body.
     */
    protected function renderBody(): void
    {
        $slowTests = $this->slow;

        $length = $this->getReportLength();
        for ($i = 1; $i <= $length; ++$i) {
            $label = key($slowTests);
            $time = array_shift($slowTests);
            $seconds = $time / 1000;

            echo sprintf(" %s) %.3fs to run %s\n", $i, $seconds, $label);
        }
    }

    /**
     * Renders slowness report footer.
     */
    protected function renderFooter(): void
    {
        if ($hidden = $this->getHiddenCount()) {
            printf("and %s more slow tests hidden from view\n", $hidden);
        }
    }

    /**
     * Populate options into class internals.
     */
    protected function loadOptions(array $options): void
    {
        $this->slowThreshold = $options['slowThreshold'] ?? 500;
        $this->reportLength = $options['reportLength'] ?? 10;
    }

    /**
     * Calculate slow test threshold for given test. A TestCase may override the
     * suite-wide slowness threshold by using the annotation {@slowThreshold}
     * with a threshold value in milliseconds.
     *
     * For example, the following test would be considered slow if its execution
     * time meets or exceeds 5000ms (5 seconds):
     *
     * <code>
     * \@slowThreshold 5000
     * public function testLongRunningProcess() {}
     * </code>
     */
    protected function getSlowThreshold(string $test): int
    {
        list($class, $testName) = explode('::', $test);
        $ann = TestUtil::parseTestMethodAnnotations($class, $testName);

        return isset($ann['method']['slowThreshold'][0]) ? (int) $ann['method']['slowThreshold'][0] : $this->slowThreshold;
    }
}
