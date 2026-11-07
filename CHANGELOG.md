# Changelog

## [1.0.1] - 2026-11-07
### Fixed
- Added the missing `tests/Unit` suite declared by `phpunit.xml`.
- Added unit coverage for explicit event subscriptions, wildcard subscriptions and empty subscription lists.

## [1.0.0] - 2026-11-07
### Added
- Outbound webhook endpoints with event subscriptions.
- HMAC-SHA256 request signatures.
- Queued delivery, attempt history and retry support.
- Laravel 12/13 PHPUnit CI matrix.
