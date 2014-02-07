# phpunit-speedtrap

Reports on slow-running tests in your PHPUnit test suite.

Enable by adding the following to your phpunit.xml:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
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
