UPGRADE FROM 3.x to 4.0
=======================

### Slowness report changes formatting of slow class names

Prior to 4.0 the slowness report displayed the qualified class name in a
human-readable format as normally seen in code:

    1. 800ms to run JohnKary\PHPUnit\Listener\Tests\SomeSlowTest:testWithDataProvider with data set "Rock"

After 4.0 the slowness report displays class names in a format ready to be
used with PHPUnit's [--filter option](https://phpunit.readthedocs.io/en/9.5/textui.html?highlight=filter)
by adding slashes to the namespace delimiter and adding a colon between the
class and method name:

    1. 800ms to run JohnKary\\PHPUnit\\Listener\\Tests\\SomeSlowTest::testWithDataProvider with data set "Rock"

An individual slow test case can now be re-run by copying and pasting the output
into a new command:

    vendor/bin/phpunit --filter 'JohnKary\\PHPUnit\\Listener\\Tests\\SomeSlowTest::testWithDataProvider with data set "Rock"'

Note that PHPUnit uses single quotes for the `--filter` option value. See the
[--filter option documentation](https://phpunit.readthedocs.io/en/9.5/textui.html?highlight=filter)
for all supported matching patterns.

UPGRADE FROM 2.x to 3.0
=======================

### `JohnKary\PHPUnit\Listener\SpeedTrapListener` subclasses must ensure method signatures match PHPUnit TestListenerDefaultImplementation

SpeedTrapListener was upgraded to support PHPUnit 7.0, which introduced a
new trait `TestListenerDefaultImplementation` containing a few new scalar type
hints and void return hints. SpeedTrapListener subclasses overriding any
of the below methods will require updating the new method signatures:

| Old signature | New signature |
| -------- | --- |
| `public function endTest(Test $test, $time)` | `public function endTest(Test $test, float $time): void`
| `public function startTestSuite(TestSuite $suite)` | `public function startTestSuite(TestSuite $suite): void`
| `public function endTestSuite(TestSuite $suite)` | `public function endTestSuite(TestSuite $suite): void`


UPGRADE FROM 1.x to 2.0
=======================

### `JohnKary\PHPUnit\Listener\SpeedTrapListener` subclasses must implement scalar type hints

SpeedTrapListener was upgraded to support PHP 7 scalar type hints. Any
subclass will need to update the overridden function signature:

* Declare strict types at the top of your subclass: `declare(strict_types=1);`
* Update method signatures:

| Old signature | New signature |
| -------- | --- |
| `protected function isSlow($time, $slowThreshold)` | `protected function isSlow(int $time, int $slowThreshold) : bool`
| `protected function addSlowTest(TestCase $test, $time)` | `protected function addSlowTest(TestCase $test, int $time)`
| `protected function hasSlowTests()` | `protected function hasSlowTests() : bool`
| `protected function toMilliseconds($time)` | `protected function toMilliseconds(float $time) : int`
| `protected function makeLabel(TestCase $test)` | `protected function makeLabel(TestCase $test) : string`
| `protected function getReportLength()` | `protected function getReportLength() : int`
| `protected function getHiddenCount()` | `protected function getHiddenCount() : int`
| `protected function getSlowThreshold(TestCase $test)` | `protected function getSlowThreshold(TestCase $test) : int`
