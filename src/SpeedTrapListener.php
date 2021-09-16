<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener;

use Exception;
use JohnKary\PHPUnit\Listener\Renderer\ConsoleRenderer;
use JohnKary\PHPUnit\Listener\Renderer\ReportRendererInterface;
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
     * Whether the test runner should halt running additional tests after
     * finding a slow test.
     *
     * @var bool
     */
    protected $stopOnSlow;


    /**
     * @var SpeedTrapReport instance responsible to grab statistics
     */
    protected $report;

    /**
     * @var ReportRendererInterface instance responsible to render report statistics
     */
    protected $reportRenderer;


    /**
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->enabled = !(getenv('PHPUNIT_SPEEDTRAP') === 'disabled');

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
            $this->report->addSlowTest($test, $timeInMilliseconds);
            if ($this->stopOnSlow) {
                $test->getTestResultObject()->stop();
            }
        }
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

        if (0 === $this->suites && $this->report->hasSlowTests()) {
            $this->reportRenderer->renderHeader();
            $this->reportRenderer->renderBody();
            $this->reportRenderer->renderFooter();
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
     * Convert PHPUnit's reported test time (microseconds) to milliseconds.
     */
    protected function toMilliseconds(float $time): int
    {
        return (int) round($time * 1000);
    }

    /**
     * Populate options into class internals.
     *
     * @throws Exception
     */
    protected function loadOptions(array $options)
    {
        $this->slowThreshold = $options['slowThreshold'] ?? 500;
        $this->stopOnSlow = $options['stopOnSlow'] ?? false;
        $this->report = new SpeedTrapReport(
            $options['reportLength'] ?? 10,
            $this->slowThreshold
        );

        if (isset($options['reportRenderer']) && is_array($options['reportRenderer'])) {
            if (empty($options['reportRenderer']['class'])) {
                throw new Exception('option reportRenderer - missing class option');
            }
            $reportRendererClass = $options['reportRenderer']['class'];
            if (!class_exists($reportRendererClass)) {
                throw new Exception("option reportRenderer class - class $reportRendererClass does not exists");
            }
            $reportRendererClassInterfaces = class_implements($reportRendererClass);
            if (!isset($reportRendererClassInterfaces[ReportRendererInterface::class])) {
                throw new Exception(
                    "option reportRenderer class - class $reportRendererClass does not implement interface "
                    . ReportRendererInterface::class
                );
            }
            $this->reportRenderer = new $reportRendererClass($this->report, $options['reportRenderer']['options'] ?? []);
        } else {
            $this->reportRenderer = new ConsoleRenderer($this->report, []);
        }
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
}
