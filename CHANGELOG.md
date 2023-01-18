# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [5.0.0] - 2023-01-18 

### Changed

-   Remove deprecated Doctrine methods.
-   Changed `isValidEntity` to `assertValidEntity`.

## [4.1.0] - 2022-11-10 

### Added

-   Added `__serialize` and `__unserialize` method to `FractalDoctrinePaginatorAdapter` to allow easier caching.

## [4.0.2] - 2022-10-24

### Fixed

-   Correct return type for `FilterManager@getValidKey()` to support an array of
    values for `key` field in ID and Keyword filters.

## [4.0.1] - 2022-10-04

### Fixed

-   Empty filter values do not result in empty WHERE clauses ([GCCDEV-4153](https://jira.gannett.com/browse/GCCDEV-4153)).

## [4.0.0] - 2022-09-12

### Changed

-   Requires PHP 8.1 or greater.
-   Updates supporting packages to meet PHP version requirements.

## [3.0.1] - 2020-11-19

### Fixed

-   Now correctly validates nested include relationships.

## [3.0.0] - 2020-11-17

### Changed

-   Updated `json-api-response` and `json-api-request` packages.

## [2.1.2] - 2020-09-02

### Fixed

-   Fixed issues causing filtering to not show entities with null relationships.

## [2.1.0] - 2019-12-09

### Changed

-   Lessened restrictions on dependency versions.
-   Updated PHPUnit and Mockery dev dependencies.
