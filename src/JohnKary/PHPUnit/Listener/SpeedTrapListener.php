<?php declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

/**
 * A PHPUnit TestListener that exposes your slowest running tests by outputting
 * results directly to the console.
 */
final class SpeedTrapListener implements TestListener
{
    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     *
     * @var int
     */
    private $suites = 0;

    /**
     * Time in milliseconds at which a test will be considered "slow" and be
     * reported by this listener.
     *
     * @var int
     */
    private $slowThreshold;

    /**
     * Number of tests to report on for slowness.
     *
     * @var int
     */
    private $reportLength;

    /**
     * Collection of slow tests.
     *
     * @var array
     */
    private $slow = [];

    public function __construct(array $options = [])
    {
        $this->loadOptions($options);
    }

    /**
     * An error occurred.
     *
     * @param Test      $test
     * @param Exception $exception
     * @param float     $time
     */
    public function addError(Test $test, Exception $exception, $time)
    {
    }

    /**
     * A warning occurred.
     *
     * @param Test $test
     * @param Warning $exception
     * @param float $time
     */
    public function addWarning(Test $test, Warning $exception, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param Test $test
     * @param AssertionFailedError $exception
     * @param float $time
     */
    public function addFailure(Test $test, AssertionFailedError $exception, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param Test $test
     * @param Exception $exception
     * @param float $time
     */
    public function addIncompleteTest(Test $test, Exception $exception, $time)
    {
    }

    /**
     * Risky test.
     *
     * @param Test $test
     * @param Exception $exception
     * @param float $time
     */
    public function addRiskyTest(Test $test, Exception $exception, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param Test $test
     * @param Exception $excetion
     * @param float $time
     */
    public function addSkippedTest(Test $test, Exception $excetion, $time)
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
    }

    /**
     * A test ended.
     *
     * @param Test $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $time = $this->toMilliseconds($time);
        $threshold = $this->getSlowThreshold($test);

        if ($this->isSlow($time, $threshold)) {
            $this->addSlowTest($test, $time);
        }
    }

    /**
     * A test suite started.
     */
    public function startTestSuite(TestSuite $suite)
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     */
    public function endTestSuite(TestSuite $suite)
    {
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
     */
    private function isSlow(int $time, int $slowThreshold) : bool
    {
        return $time >= $slowThreshold;
    }

    /**
     * Stores a test as slow.
     */
    private function addSlowTest(TestCase $test, int $time)
    {
        $label = $this->makeLabel($test);

        $this->slow[$label] = $time;
    }

    /**
     * Whether at least one test has been considered slow.
     */
    private function hasSlowTests() : bool
    {
        return !empty($this->slow);
    }

    /**
     * Convert PHPUnit's reported test time (microseconds) to milliseconds.
     *
     * @param float $time
     */
    private function toMilliseconds($time) : int
    {
        return (int) round($time * 1000);
    }

    /**
     * Label for describing a test.
     */
    private function makeLabel(TestCase $test) : string
    {
        return sprintf('%s:%s', get_class($test), $test->getName());
    }

    /**
     * Calculate number of slow tests to report about.
     */
    private function getReportLength() : int
    {
        return min(count($this->slow), $this->reportLength);
    }

    /**
     * Find how many slow tests occurred that won't be shown due to list length.
     */
    private function getHiddenCount() : int
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
     * Renders slow test report header.
     */
    private function renderHeader()
    {
        echo sprintf(
            "\n\nYou should really fix these slow tests (>%sms)...\n",
            $this->slowThreshold
        );
    }

    /**
     * Renders slow test report body.
     */
    private function renderBody()
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
     * Renders slow test report footer.
     */
    private function renderFooter()
    {
        if ($hidden = $this->getHiddenCount()) {
            echo sprintf(
                '...and there %s %s more above your threshold hidden from view',
                $hidden == 1 ? 'is' : 'are',
                $hidden
            );
        }
    }

    /**
     * Populate options into class internals.
     */
    private function loadOptions(array $options)
    {
        $this->slowThreshold = $options['slowThreshold'] ?? 500;
        $this->reportLength = $options['reportLength'] ?? 10;
    }

    /**
     * Get slow test threshold for given test. A TestCase can override the
     * suite-wide slow threshold by using the annotation @slowThreshold with
     * the threshold value in milliseconds.
     *
     * The following test will only be considered slow when its execution time
     * reaches 5000ms (5 seconds):
     *
     * <code>
     * \@slowThreshold 5000
     * public function testLongRunningProcess() {}
     * </code>
     */
    private function getSlowThreshold(TestCase $test) : int
    {
        $annotations = $test->getAnnotations();

        return $annotations['method']['slowThreshold'][0] ?? $this->slowThreshold;
    }
}
