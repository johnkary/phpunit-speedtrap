<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener;

use PHPUnit\Framework\{TestListener, TestListenerDefaultImplementation, TestSuite, Test, TestCase};
use PHPUnit\Util\Test as TestUtil;

/**
 * A PHPUnit TestListener that exposes your slowest running tests by outputting
 * results directly to the console.
 */
class SpeedTrapListener implements TestListener
{
    use TestListenerDefaultImplementation;

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
     * Whether the test runner should halt running additional tests after
     * finding a slow test.
     *
     * @var bool
     */
    protected $stopOnSlow;

    /**
     * Collection of slow tests.
     * Keys (string) => Printable label describing the test
     * Values (int) => Test execution time, in milliseconds
     */
    protected $slow = [];

    /**
     * Total test execution time so far, in milliseconds
     * @var int
     */
    protected $totalTime = 0;

    public function __construct(array $options = [])
    {
        $this->enabled = getenv('PHPUNIT_SPEEDTRAP') === 'disabled' ? false : true;

        $this->loadOptions($options);
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$this->enabled) return;
        if (!$test instanceof TestCase) return;

        $timeInMilliseconds = $this->toMilliseconds($time);
        $threshold = $this->getSlowThreshold($test);

        if ($this->isSlow($timeInMilliseconds, $threshold)) {
            $this->addSlowTest($test, $timeInMilliseconds);
        }

        $this->totalTime += $timeInMilliseconds;
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if (!$this->enabled) return;

        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (!$this->enabled) return;

        $this->suites--;

        if (0 === $this->suites && $this->hasSlowTests()) {
            arsort($this->slow); // Sort longest running tests to the top

            $this->renderHeader();
            $this->renderBody();
            $this->renderAnyHiddenSlowTest();
            $this->renderStats();
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
     */
    protected function addSlowTest(TestCase $test, int $time)
    {
        $label = $this->makeLabel($test);

        $this->slow[$label] = $time;

        if ($this->stopOnSlow) {
            $test->getTestResultObject()->stop();
        }
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
     *     vendor/bin/phpunit --filter 'JohnKary\\PHPUnit\\Listener\\Tests\\SomeSlowTest::testWithDataProvider with data set "Rock"'
     */
    protected function makeLabel(TestCase $test): string
    {
        return sprintf('%s::%s', addslashes(get_class($test)), $test->getName());
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
    protected function renderHeader()
    {
        echo sprintf("\n\nYou should really speed up these slow tests (>%sms)...\n", $this->slowThreshold);
    }

    /**
     * Renders slowness report body.
     */
    protected function renderBody()
    {
        $slowTests = $this->slow;

        $length = $this->getReportLength();
        for ($i = 1; $i <= $length; ++$i) {
            $label = key($slowTests);
            $time = array_shift($slowTests);

            echo sprintf(" %s. %sms to run %s\n", $i, $time, $label);
        }
    }

    /**
     * Renders slowness report footer.
     */
    protected function renderAnyHiddenSlowTest()
    {
        if ($hidden = $this->getHiddenCount()) {
            printf("...and there %s %s more above your threshold hidden from view\n", $hidden == 1 ? 'is' : 'are', $hidden);
        }
    }

    /**
     * Populate options into class internals.
     */
    protected function loadOptions(array $options)
    {
        $this->slowThreshold = $options['slowThreshold'] ?? 500;
        $this->reportLength = $options['reportLength'] ?? 10;
        $this->stopOnSlow = $options['stopOnSlow'] ?? false;
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
    protected function getSlowThreshold(TestCase $test): int
    {
        $ann = TestUtil::parseTestMethodAnnotations(
            get_class($test),
            $test->getName(false)
        );

        return isset($ann['method']['slowThreshold'][0]) ? (int) $ann['method']['slowThreshold'][0] : $this->slowThreshold;
    }

    private function renderStats(): void
    {

        $totalSlowTimeMs = array_sum($this->slow);
        $totalFastTimeMs = $this->totalTime - $totalSlowTimeMs;

        $totalFastTimeSeconds  = $totalFastTimeMs / 1000;
        $totalSlowTimeSeconds  = $totalSlowTimeMs / 1000;

        echo PHP_EOL;

        printf(
            " Fast tests: %.1f seconds (%.2f%%)\n",
            $totalFastTimeSeconds,
            100 * $totalFastTimeMs / $this->totalTime
        );

        printf(
            " Slow tests: %.1f seconds (%.2f%%)\n",
            $totalSlowTimeSeconds,
            100 * $totalSlowTimeMs / $this->totalTime
        );
    }
}
