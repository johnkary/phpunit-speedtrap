# phpunit-speedtrap

SpeedTrap reports on slow-running tests in your PHPUnit test suite right in your console.

Many factors affect test execution time. A test not properly isolated from variable latency (database, network, etc.) and even basic load on your test machine will cause test times to fluctuate.

SpeedTrap helps you **identify slow tests** but cannot tell you **why** those tests are slow. For that you should check out something like [PHPUnit\_Listener\_XHProf](https://github.com/sebastianbergmann/phpunit-testlistener-xhprof) to help identify specifically which methods in your call stack are slow.

![Screenshot of terminal using SpeedTrap](http://i.imgur.com/Zr34giR.png)

## Installation

SpeedTrap is installable via [Composer](http://getcomposer.org) and should be added as a `require-dev` dependency:

```console
php composer.phar require-dev johnkary/phpunit-speedtrap dev-master
```

## Usage

Enabled it with all defaults by adding the following to your test suite's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener" />
    </listeners>
</phpunit>
```

Now run your test suite as normal. If tests run that exceed the slowness threshold (500ms by default), SpeedTrap will report on them in the console after the suite completes.

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
                    <element key="slowThreshold"> <!-- Slowness threshold in ms -->
                        <integer>500</integer>
                    </element>
                    <element key="reportLength"> <!-- Number of slow tests to report on -->
                        <integer>5</integer>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

This allows you to set your own criteria for "slow" tests, and how many you care to know about.

## Inspiration

This project was inspired by Rspec's `-p` option that displays feedback about slow tests.

## License

phpunit-speedtrap is available under the MIT License.
