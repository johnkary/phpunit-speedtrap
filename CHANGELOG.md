CHANGELOG
=================

View diff for a specific commit:  
https://github.com/johnkary/phpunit-speedtrap/commit/XXX where XXX is the commit hash

View diff between two versions:  
https://github.com/johnkary/phpunit-speedtrap/compare/v3.2.0...v3.3.0

## 3.3.0 (2020-12-18)

Version 3.3 adds supports for PHPUnit 9.5+, and a way to enable or disable the SpeedTrap listener using environment variables.

* [PR #73](https://github.com/johnkary/phpunit-speedtrap/pull/73) Compatibility with PHPUnit 9.5
* [PR #66](https://github.com/johnkary/phpunit-speedtrap/pull/66) Environment variable PHPUNIT_SPEEDTRAP="disabled" can disable profiling

## 3.2.0 (2020-02-12)

Version 3.2 introduces supports for PHPUnit 9.0+.
If your use of SpeedTrap depends on specific text output from SpeedTrap slowness
report, see below wording changes that may require updating your implementation.

* [PR #57](https://github.com/johnkary/phpunit-speedtrap/pull/57) Wording change to slowness report in renderHeader()

## 3.1.0 (2019-02-23)

Version 3.1 introduces support for PHPUnit 8.0+.

## 3.0.0 (2018-02-24)

Version 3.0 introduces support for PHPUnit 7.0+ and PHP 7.1+.

Changes may be required if you have extended SpeedTrapListener. See
[UPGRADE.md](UPGRADE.md) for upgrading your subclass to support 3.0.

* [PR #41](https://github.com/johnkary/phpunit-speedtrap/pull/41) Make compatible with phpunit 7.x

## 2.0.0 (2017-12-06)

Version 2.0 introduces support for PHPUnit 6.0+, PHP 7.0+ and PSR-4 autoloading.

Changes are required if you have extended SpeedTrapListener. See
[UPGRADE.md](UPGRADE.md) for upgrading your subclass to support 2.0.

* [PR #26](https://github.com/johnkary/phpunit-speedtrap/pull/26) Support PHPUnit 6.0
* [PR #30](https://github.com/johnkary/phpunit-speedtrap/pull/30) Add PHP 7 features (includes backwards compatibility breaks, see UPGRADE.md)
* [PR #31](https://github.com/johnkary/phpunit-speedtrap/pull/31) Support PSR-4 autoloading
* [PR #39](https://github.com/johnkary/phpunit-speedtrap/pull/39) SpeedTrapListener extends PHPUnit BaseTestListener
