# Changelog

All notable changes to this project will be documented in this file.

## [1.1.2]
Fixed
- A nested object or collection item whose `#[Identifier]` column is `null` is now skipped. Before, if any other column of that prefix had a value (e.g. a LEFT JOIN row with a name but no id), it was hydrated as an entity with id `""`, and all such rows were merged into one object.

Development:
- `bin/requirements.txt`, `bin/Pipfile` and `bin/Pipfile.lock` reduced from a full Ubuntu system snapshot to the three packages the scripts use (`click`, `rich`, `python-dotenv`); Python 3.10+.
- README: CI, version, PHP, downloads, license, PHPStan and Psalm badges and a "Continuous integration" section.
- Tests: 6 → 85 tests, line coverage 89% → 99%. Added `tests/Unit` for all small components and `MapperBehaviorTest` for focused mapper scenarios (Traversable input, `#[Column]`, `#[Ignore]`, `SnakeToCamel`, custom/unregistered transformer, null joins, shared identity, grouping, lazy collections). `testSuccessWithLazy` now really tests `MediaLazyDto`. Removed `tests/TODO` (all items implemented).
- GitHub Actions workflow (`.github/workflows/ci.yml`): a quality job (composer validate, composer-unused, PHPStan, Psalm, Rector and PHP CS Fixer in check mode) on PHP 8.2 and a PHPUnit matrix on PHP 8.2–8.5 (8.5 allowed to fail).
- Fixed `bin/ci.py`: added the missing `phpunit-ci` composer script.
- `bin/ci.py` now also validates composer, runs `composer-unused` and Rector, and runs php-cs-fixer and Rector in check (dry-run) mode so they can fail the pipeline.
- New composer scripts: `phpunit-ci`, `rector-check`, `php-cs-fixer-check`, `composer-unused`.
- Package supports PHP `^8.2`; Psalm now analyzes against PHP 8.2 and PHPUnit schema matches PHPUnit 10.5.
- Docker: PHP version selectable with `PHP_VERSION` (default 8.4, e.g. `PHP_VERSION=8.2 make up`); the image builds without `composer.lock` and on PHP 8.5 (OPcache is built in there, so it is no longer installed unconditionally).
- Test namespace corrected to `ZJKiza\FlatMapper\Tests\Functional`.

## [1.1.1]
Improved for developers. 
For developers added:
- a makefile for those who don't have python v3.10,
- additional values for container build,
- .env.dist,
- speed tests updated with which CPU they were run on.

## [1.1.0]
- Micro code optimize
- Added support that input data besides array can also be Traversable. Support for iterable.

Development:
- Fix docker container for opcache for development

## [1.0.0]
- Code speedup by 18%

## [0.6.1]
Fixed
- Correction of documentation for use in Symfony/Laravel framework 

## [0.6.0]
Fixed
- compatibility

## [0.5.1]
Code optimized

## [0.5.0]
Initial version