# phpunit-speedtrap

Reports on slow-running tests in your PHPUnit test suite.

Enable by adding the following to your phpunit.xml:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="JohnKary\PHPUnit\Listener\SpeedTrapListener">
            <array>
                <integer>500</integer> <!-- Slowness threshold in ms -->
                <integer>10</integer>  <!-- Number of slow tests to report on -->
            </array>
        </listener>
    </listeners>
</phpunit>
```
