# Release guide

## Repository
- Repository: `ITMOSOFT/laravel-webhooks`
- Description: `Signed outbound webhook delivery, retries and delivery history for Laravel applications.`

## v1.0.1
Title: `v1.0.1 — Add Missing Unit Test Suite`

### Release notes
- Add the missing `tests/Unit` directory referenced by PHPUnit configuration.
- Add unit tests for endpoint event subscription matching, including wildcard behavior.

### Before publishing
1. Run `composer install`.
2. Run `composer test`.
3. Push the branch and verify Laravel 12 and Laravel 13 GitHub Actions jobs pass.
4. Create and push tag:
   ```bash
   git tag -a v1.0.1 -m "Add missing unit test suite"
   git push origin v1.0.1
   ```
5. Create GitHub Release using the title and notes above.
6. Submit/update `itmosoft/laravel-webhooks` on Packagist.

Do not publish until both CI matrix jobs pass.
