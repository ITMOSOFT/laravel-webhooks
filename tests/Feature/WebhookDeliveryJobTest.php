<?php

namespace ItmoSoft\Webhooks\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ItmoSoft\Webhooks\Jobs\DeliverWebhookJob;
use ItmoSoft\Webhooks\Models\WebhookDelivery;
use ItmoSoft\Webhooks\Models\WebhookEndpoint;
use ItmoSoft\Webhooks\Tests\TestCase;

class WebhookDeliveryJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => false,
        ])->run();
    }

    public function test_it_sends_signed_request_and_records_success(): void
    {
        Http::fake(['example.test/*' => Http::response(['ok' => true], 200)]);

        $endpoint = WebhookEndpoint::create([
            'url' => 'https://example.test/hook',
            'secret' => 'secret-key',
            'events' => ['order.created'],
            'is_active' => true,
        ]);

        $delivery = WebhookDelivery::create([
            'endpoint_id' => $endpoint->id,
            'event_id' => (string) Str::uuid(),
            'event_type' => 'order.created',
            'payload' => ['order_id' => 1],
            'status' => 'pending',
            'attempt_count' => 0,
            'next_attempt_at' => now(),
        ]);

        (new DeliverWebhookJob($delivery->id))->handle();

        $delivery->refresh();

        $this->assertSame('delivered', $delivery->status);
        $this->assertSame(200, $delivery->last_http_status);
        $this->assertDatabaseHas('itmosoft_webhook_delivery_attempts', [
            'delivery_id' => $delivery->id,
            'attempt_number' => 1,
            'http_status' => 200,
        ]);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-ItmoSoft-Signature')
                && str_starts_with($request->header('X-ItmoSoft-Signature')[0], 'sha256=');
        });
    }
}
