<?php

namespace ItmoSoft\Webhooks\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ItmoSoft\Webhooks\Facades\Webhooks;
use ItmoSoft\Webhooks\Jobs\DeliverWebhookJob;
use ItmoSoft\Webhooks\Models\WebhookEndpoint;
use ItmoSoft\Webhooks\Tests\TestCase;

class WebhookDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    public function test_it_creates_delivery_for_matching_active_endpoint(): void
    {
        Queue::fake();

        WebhookEndpoint::create([
            'url' => 'https://example.test/webhook',
            'secret' => 'secret-key',
            'events' => ['order.created'],
            'is_active' => true,
        ]);

        Webhooks::dispatch('order.created', ['order_id' => 42]);

        $this->assertDatabaseHas('itmosoft_webhook_deliveries', [
            'event_type' => 'order.created',
            'status' => 'pending',
        ]);

        Queue::assertPushed(DeliverWebhookJob::class);
    }

    public function test_it_does_not_dispatch_unsubscribed_events(): void
    {
        Queue::fake();

        WebhookEndpoint::create([
            'url' => 'https://example.test/webhook',
            'secret' => 'secret-key',
            'events' => ['invoice.paid'],
            'is_active' => true,
        ]);

        Webhooks::dispatch('order.created', ['order_id' => 42]);

        $this->assertDatabaseCount('itmosoft_webhook_deliveries', 0);
        Queue::assertNothingPushed();
    }
}
