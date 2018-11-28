# phpunit-speedtrap

[![Build Status](https://travis-ci.org/johnkary/phpunit-speedtrap.svg?branch=master)](https://travis-ci.org/johnkary/phpunit-speedtrap)

SpeedTrap reports on slow-running PHPUnit tests right in the console.

Many factors affect test execution time. A test not properly isolated from variable latency (database, network, etc.) and even basic load on the test machine will cause test execution times to fluctuate.

SpeedTrap helps **identify slow tests** but cannot explain **why** those tests are slow. For that consider using [Blackfire.io](https://blackfire.io) to profile the test suite, or another PHPUnit listener [PHPUnit\_Listener\_XHProf](https://github.com/sebastianbergmann/phpunit-testlistener-xhprof), to specifically identify slow code.

![Screenshot of terminal using SpeedTrap](http://i.imgur.com/Zr34giR.png)

## Installation

SpeedTrap is installable via [Composer](http://getcomposer.org) and should be added as a `require-dev` dependency:

    composer require --dev johnkary/phpunit-speedtrap


## Usage

Enable with all defaults by adding the following code to your project's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
    </listeners>
</phpunit>
```

Now run the test suite as normal. If one or more test executions exceed the slowness threshold (500ms by default), SpeedTrap will report on those tests in the console after all tests have completed.

## Configuration

SpeedTrap has two configurable parameters:

* **slowThreshold** - Number of milliseconds a test takes to execute before being considered "slow" (Default: 500ms)
* **reportLength** - Number of slow tests included in the report (Default: 10 tests)

These configuration parameters are set in `phpunit.xml` when adding the listener:

```xml
<phpunit bootstrap="vendor/autoload.php">
    <!-- ... other suite configuration here ... -->

    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener">
            <arguments>
                <array>
                    <element key="slowThreshold">
                        <integer>500</integer>
                    </element>
                    <element key="reportLength">
                        <integer>5</integer>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

This allows customizing what the project considers a "slow" test and how many are reported on to project maintainers.

## Custom slowness threshold per-test case

Some projects have a few complex tests that take a long time to run. It is possible to set a different slowness threshold for individual test cases.

Use the annotation `@slowThreshold` to set a custom slowness threshold for single test cases. This number may be higher or lower than the default threshold and will be used in place of the default threshold for that specific test.

```php
class SomeTestCase extends PHPUnit\Framework\TestCase
{
    /**
     * @slowThreshold 5000
     */
    public function testLongRunningProcess()
    {
        // Code that takes a longer time to execute
    }
}
```

## Inspiration

This project was inspired by [RSpec's](https://github.com/rspec/rspec) `--profile` option that displays feedback about slow tests.

## License

phpunit-speedtrap is available under the MIT License.
