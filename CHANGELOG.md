# 1.0.7

## Major Changes

- JPUP no longer recognizes the `--trace` option.

## Minor Changes

- Added `Test_Browser_Listener_Callback` for injecting event handlers into browser execution.
- Check for `sfDoctrineGuardPlugin` before proceeding in `Test_Browser->signin()`.
- Set `error_reporting` automatically when installed.

# 1.0.6

## Major Changes

- Fixed output buffering issues with PHPUnit 3.6 once and for all.

## Minor Changes

- Better formatting of assertStatusCode() failure message.
- Fixed IDE warnings, added @method declarations for object wrappers, fixed type
    hinting where inaccurate.
- Updated email address in @author tags.

# 1.0.5

## Issues

- Resolved [#17]: Make generating skeleton tests for inherited methods an option.

## Major Changes

- Added support for PHPUnit 3.6, dropped support for earlier versions.
- Introduced `Test_Browser_Plugin_Logger`.
- Fixed issues when trying to run tests for multiple applications in the same test run.

## Minor Changes

- If `--trace` option is provided, display full stack traces in test failure/error reports.
- Log a message to `sfLogger` when the user is logged in via `Test_Browser->signin()`.
- Added changelog to package.xml template.

# 1.0.4

## Issues

- Resolved [#19]: Need a changelog.
- Resolved [#6]:  Remove MySQL dependency.
- [#3]:  Added --plugin option to runner tasks (generators still outstanding).

## Major Changes

- Added `Test_Browser->signin()`.
- Empty `--filter` value no longer breaks PHPUnit.
- Added error/status code to `assertStatusCode()` failure message.
- Added `sf_fixture_dir` `sfConfig` value (needs documentation).

## Minor Changes

- Added `__toString()` handler to `Test_ObjectWrapper`.
- Convert parameters to strings before sending them to `sfBrowser->call()`.
- Fixed typos in skeleton test case files.
- Minor documentation updates.

# 1.0.0

- Initial release.