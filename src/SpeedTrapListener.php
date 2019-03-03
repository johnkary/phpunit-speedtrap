<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener;

use PHPUnit\Framework\{TestListener, TestListenerDefaultImplementation, TestSuite, Test, TestCase};

/**
 * A PHPUnit TestListener that exposes your slowest running tests by outputting
 * results directly to the console or output file..
 */
class SpeedTrapListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     */
    protected $suites = 0;

    /**
     * Output path
     *
     * @var string
     */
    protected $outPath;

    /**
     * Output descriptor
     *
     * @var resource
     */
    protected $out;

    /**
     * If True, flush output after every write.
     *
     * @var boolean
     */
    protected $forceFlush;

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
        $this->loadOptions($options);

        $this->out = fopen($this->outPath, 'wt');
    }

    /**
     * Destruct the instance
     */
    public function __destruct()
    {
        fclose($this->out);
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase) return;

        $timeInMilliseconds = $this->toMilliseconds($time);
        $threshold = $this->getSlowThreshold($test);

        if ($this->isSlow($timeInMilliseconds, $threshold)) {
            $this->addSlowTest($test, $timeInMilliseconds);
        }
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
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
     *
     * @param int $time          Test execution time in milliseconds
     * @param int $slowThreshold Test execution time at which a test should be considered slow, in milliseconds
     */
    protected function isSlow(int $time, int $slowThreshold): bool
    {
        return $time >= $slowThreshold;
    }

    /**
     * Stores a test as slow.
     */
    protected function addSlowTest(TestCase $test, int $time)
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
     * Label describing a test.
     */
    protected function makeLabel(TestCase $test): string
    {
        return sprintf('%s:%s', get_class($test), $test->getName());
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
        $this->write(sprintf("\n\nYou should really fix these slow tests (>%sms)...\n", $this->slowThreshold));
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

            $this->write(sprintf(" %s. %sms to run %s\n", $i, $time, $label));
        }
    }

    /**
     * Renders slowness report footer.
     */
    protected function renderFooter()
    {
        if ($hidden = $this->getHiddenCount()) {
            $this->write(sprintf("...and there %s %s more above your threshold hidden from view",
                                 $hidden == 1 ? 'is' : 'are', $hidden));
        }
    }

    /**
     * Populate options into class internals.
     */
    protected function loadOptions(array $options)
    {
        $this->outPath = $options['outPath'] ?? 'php://stdout';
        $this->forceFlush = $options['forceFlush'] ?? false;
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
    protected function getSlowThreshold(TestCase $test): int
    {
        $ann = $test->getAnnotations();

        return isset($ann['method']['slowThreshold'][0]) ? (int) $ann['method']['slowThreshold'][0] : $this->slowThreshold;
    }

    /**
     * Write text to output
     *
     * @param string $buffer
     */
    protected function write($buffer)
    {
        fwrite($this->out, $buffer);

        if ($this->forceFlush)
            fflush($this->out);
    }
}
