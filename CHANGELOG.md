CHANGELOG
=================

View diff for a specific commit:  
https://github.com/johnkary/phpunit-speedtrap/commit/XXX where XXX is the commit hash

View diff between two versions:  
https://github.com/johnkary/phpunit-speedtrap/compare/v2.0.0...v3.0.0

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
