# phpunit-speedtrap

[![Integrate](https://github.com/johnkary/phpunit-speedtrap/workflows/Integrate/badge.svg?branch=master)](https://github.com/johnkary/phpunit-speedtrap/actions)

SpeedTrap reports on slow-running PHPUnit tests right in the console.

Many factors affect test execution time. A test not properly isolated from variable latency (database, network, etc.) and even basic load on the test machine will cause test execution times to fluctuate.

SpeedTrap helps **identify slow tests** but cannot explain **why** those tests are slow. For that consider using [Blackfire.io](https://blackfire.io) to profile the test suite, or another PHPUnit listener [PHPUnit\_Listener\_XHProf](https://github.com/sebastianbergmann/phpunit-testlistener-xhprof), to specifically identify slow code.

![Screenshot of terminal using SpeedTrap](https://i.imgur.com/xSpWL4Z.png)

## Installation

SpeedTrap is installed using [Composer](http://getcomposer.org). Add it as a `require-dev` dependency:

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

Now run the test suite. If one or more test executions exceed the slowness threshold (500ms by default), SpeedTrap will report on those tests in the console after all tests have completed.

## Config Parameters

SpeedTrap also supports these parameters:

* **slowThreshold** - Number of milliseconds when a test is considered "slow" (Default: 500ms)
* **reportLength** - Number of slow tests included in the report (Default: 10 tests)
* **stopOnSlow** - Stop execution upon first slow test (Default: false). Activate by setting "true".

Each parameter is set in `phpunit.xml`:

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
                        <integer>10</integer>
                    </element>
                    <element key="stopOnSlow">
                        <boolean>false</boolean>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

## Custom slowness threshold per-test case

Some projects have a few complex tests that take a long time to run. It is possible to set a different slowness threshold for individual test cases.

The annotation `@slowThreshold` can set a custom slowness threshold for each test case. This number may be higher or lower than the default threshold and is used instead of the default threshold for that specific test.

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

Setting `@slowThreshold 0` will never report that test as slow.

## Disable slowness profiling using an environment variable

SpeedTrapListener profiles for slow tests when enabled in phpunit.xml. But using an environment variable named `PHPUNIT_SPEEDTRAP` can enable or disable the listener.

    $ PHPUNIT_SPEEDTRAP="disabled" ./vendor/bin/phpunit

#### Use case: Disable profiling in development, but profile with Travis CI

Travis CI is popular for running tests in the cloud after pushing new code to a repository.

Step 1) Enable SpeedTrapListener in phpunit.xml, but set `PHPUNIT_SPEEDTRAP="disabled"` to disable profiling when running tests.

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <php>
        <env name="PHPUNIT_SPEEDTRAP" value="disabled" />
    </php>

    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
    </listeners>
</phpunit>
```

Step 2) Configure `.travis.yml` with `PHPUNIT_SPEEDTRAP="enabled"` to profile for slow tests when running on Travis CI:

```yaml
language: php

php:
  - 7.3

env:
  - PHPUNIT_SPEEDTRAP="enabled"
```

Step 3) View the Travis CI build output and read the slowness report printed in the console.

[Travis CI Documentation - Environment Variables](https://docs.travis-ci.com/user/environment-variables)

#### Use case: Enable profiling in development, but disable with Travis CI

Step 1) Enable SpeedTrapListener in phpunit.xml. The slowness report will output during all test suite executions.

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
    </listeners>
</phpunit>
```

Step 2) Configure `.travis.yml` with `PHPUNIT_SPEEDTRAP="disabled"` to turn off profiling when running on Travis CI:

```yaml
language: php

php:
  - 7.3

env:
  - PHPUNIT_SPEEDTRAP="disabled"
```

Step 3) View the Travis CI build output and confirm the slowness report is not printed in the console.

#### Use case: Only enable SpeedTrapListener on demand via command-line

Useful when you only want to profile slow tests once in a while.

Step 1) Setup phpunit.xml to enable SpeedTrapListener, but disable slowness profiling by setting `PHPUNIT_SPEEDTRAP="disabled"` like this:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <php>
        <env name="PHPUNIT_SPEEDTRAP" value="disabled" />
    </php>

    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
    </listeners>
</phpunit>
```

Step 2) When executing `phpunit` from the command-line, enable slowness profiling only for this run by passing the environment variable `PHPUNIT_SPEEDTRAP="enabled"` like this:

```bash
$ PHPUNIT_SPEEDTRAP=enabled ./vendor/bin/phpunit
```

## Using with Symfony Framework

[Symfony Framework](https://symfony.com/) comes with package [symfony/phpunit-bridge](https://packagist.org/packages/symfony/phpunit-bridge) that installs its own version of PHPUnit and **ignores** what is defined in your project's composer.json or composer.lock file. See the PHPUnit versions it installs with command `ls vendor/bin/.phpunit/`

symfony/phpunit-bridge allows environment variable `SYMFONY_PHPUNIT_REQUIRE` to define additional dependencies while installing phpunit.

The easiest way to set environment variables for the script `simple-phpunit` is via phpunit.xml.dist:

phpunit.xml.dist
```xml
<phpunit bootstrap="vendor/autoload.php">
    <php>
        <env name="SYMFONY_PHPUNIT_REQUIRE" value="johnkary/phpunit-speedtrap:^4"/>
        <env name="SYMFONY_PHPUNIT_VERSION" value="9"/>
    </php>
</phpunit>
```
(add the listener as described above)

Using the above example, running `vendor/bin/simple-phpunit` will now install the latest PHPUnit 9 and require the latest phpunit-speedtrap v4.

## Development

Follow these steps to add new features or develop your own fork:

```
# Get source code (or replace with your fork URL)
$ git checkout https://github.com/johnkary/phpunit-speedtrap.git phpunit-speedtrap

# Install dev dependencies
$ cd phpunit-speedtrap
$ composer install

# Run test suite to verify code runs as expected
$ vendor/bin/phpunit
```

## Inspiration

SpeedTrap was inspired by [RSpec's](https://github.com/rspec/rspec) `--profile` option that displays feedback about slow tests.

## License

phpunit-speedtrap is available under the MIT License.
