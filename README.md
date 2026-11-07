# ITMOSOFT Laravel Webhooks

Signed outbound webhook delivery for Laravel 12 and 13.

## Features

- Register endpoint URLs and per-endpoint signing secrets.
- Subscribe endpoints to named event types (`*` supported).
- Queue event deliveries with a stable event UUID.
- Sign the exact JSON request body with HMAC-SHA256.
- Persist delivery attempts, HTTP response status and response excerpt.
- Retry failed deliveries manually or with the included retry command.
- Configurable table prefix, timeout and retry policy.

## Install

```bash
composer require itmosoft/laravel-webhooks
php artisan vendor:publish --tag=webhooks-config
php artisan vendor:publish --tag=webhooks-migrations
php artisan migrate
```

The service provider also loads package migrations automatically. Publishing is optional unless you need to customize them.

## Register an endpoint

```php
use ItmoSoft\Webhooks\Models\WebhookEndpoint;

$endpoint = WebhookEndpoint::create([
    'url' => 'https://example.com/hooks/orders',
    'secret' => bin2hex(random_bytes(32)),
    'events' => ['order.created', 'order.paid'],
    'is_active' => true,
]);
```

Store secrets securely and never expose them in API responses or logs.

## Dispatch an event

```php
use ItmoSoft\Webhooks\Facades\Webhooks;

Webhooks::dispatch('order.created', [
    'order_id' => 123,
    'total' => 4999,
]);
```

Matching active endpoints receive queued `DeliverWebhookJob` jobs. The request includes:

- `X-ItmoSoft-Event`: stable event UUID
- `X-ItmoSoft-Event-Type`: event name
- `X-ItmoSoft-Timestamp`: Unix timestamp
- `X-ItmoSoft-Signature`: `sha256=` followed by the HMAC-SHA256 digest of the exact raw JSON body

The signature is calculated as `hash_hmac('sha256', $rawBody, $endpointSecret)`. Receivers should compare signatures with a timing-safe comparison and enforce a timestamp tolerance to mitigate replay attacks.

## Retry failed deliveries

```php
use ItmoSoft\Webhooks\Models\WebhookDelivery;
use ItmoSoft\Webhooks\Jobs\DeliverWebhookJob;

$delivery = WebhookDelivery::findOrFail($id);
DeliverWebhookJob::dispatch($delivery->id);
```

A scheduled retry command is also provided:

```bash
php artisan webhooks:retry
```

It queues eligible failed deliveries whose `next_attempt_at` is due. Schedule it in your application scheduler, for example every minute.

## Testing

```bash
composer install
composer test
```

GitHub Actions runs PHPUnit against Laravel 12 and 13. The dedicated PHPUnit step fails the workflow if any test fails.

## Operational notes

- Configure a real queue worker; do not use the `sync` driver in production.
- The package stores response excerpts, not complete response bodies.
- Use HTTPS endpoints only in production. Add endpoint URL allowlisting/SSRF protections appropriate to your environment before allowing untrusted users to register URLs.
- Retries are at-least-once, not exactly-once. Receivers must be idempotent using `X-ItmoSoft-Event`.
- Delivery jobs use the queue's normal retry/timeout configuration. The package retry command is a separate, explicit retry mechanism.
- This package sends outbound webhooks. It does not implement inbound webhook signature verification.
