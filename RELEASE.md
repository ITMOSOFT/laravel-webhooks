# Release guide

## Repository
- Repository: `ITMOSOFT/laravel-webhooks`
- Description: `Signed outbound webhook delivery, retries and delivery history for Laravel applications.`

## v1.0.0
Title: `v1.0.0 — Initial Release`

### Release notes
- Add endpoint registration and event subscriptions.
- Add queued outbound delivery with HMAC-SHA256 signatures.
- Persist delivery attempts and HTTP response metadata.
- Add manual retry and scheduled retry command.
- Support Laravel 12 and 13.

### Before publishing
1. Run `composer install`.
2. Run `composer test`.
3. Push the branch and verify Laravel 12 and Laravel 13 GitHub Actions jobs pass.
4. Create and push tag:
   ```bash
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```
5. Create GitHub Release using the title and notes above.
6. Submit `itmosoft/laravel-webhooks` to Packagist and configure the GitHub webhook.

Do not publish until both CI matrix jobs pass.
