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
