<?php

namespace ItmoSoft\Webhooks\Services;

use Illuminate\Support\Str;
use ItmoSoft\Webhooks\Jobs\DeliverWebhookJob;
use ItmoSoft\Webhooks\Models\WebhookDelivery;
use ItmoSoft\Webhooks\Models\WebhookEndpoint;

class WebhookManager
{
    public function dispatch(string $event, array $payload): string
    {
        $eventId = (string) Str::uuid();

        WebhookEndpoint::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (WebhookEndpoint $endpoint) => $endpoint->subscribesTo($event))
            ->each(function (WebhookEndpoint $endpoint) use ($event, $eventId, $payload) {
                $delivery = WebhookDelivery::query()->create([
                    'endpoint_id' => $endpoint->getKey(),
                    'event_id' => $eventId,
                    'event_type' => $event,
                    'payload' => $payload,
                    'status' => 'pending',
                    'attempt_count' => 0,
                    'next_attempt_at' => now(),
                ]);

                DeliverWebhookJob::dispatch($delivery->getKey());
            });

        return $eventId;
    }
}
