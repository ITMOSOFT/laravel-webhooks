<?php

namespace ItmoSoft\Webhooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use ItmoSoft\Webhooks\Models\WebhookDelivery;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly int $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::query()->with('endpoint')->find($this->deliveryId);

        if (! $delivery || ! $delivery->endpoint || ! $delivery->endpoint->is_active) {
            return;
        }

        $endpoint = $delivery->endpoint;
        $body = json_encode([
            'id' => $delivery->event_id,
            'type' => $delivery->event_type,
            'created_at' => $delivery->created_at?->toISOString(),
            'data' => $delivery->payload,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $endpoint->secret);

        $delivery->forceFill([
            'status' => 'sending',
            'attempt_count' => $delivery->attempt_count + 1,
        ])->save();

        try {
            $response = Http::timeout((int) config('webhooks.timeout', 10))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-ItmoSoft-Event' => $delivery->event_id,
                    'X-ItmoSoft-Event-Type' => $delivery->event_type,
                    'X-ItmoSoft-Timestamp' => $timestamp,
                    'X-ItmoSoft-Signature' => 'sha256='.$signature,
                ])->withBody($body, 'application/json')
                ->post($endpoint->url);

            $success = $response->successful();
            $delay = config('webhooks.retry_delay_seconds', [60, 300, 900, 3600]);
            $maxAttempts = (int) config('webhooks.max_attempts', 5);
            $excerptLength = (int) config('webhooks.response_excerpt_length', 2000);

            $delivery->attempts()->create([
                'attempt_number' => $delivery->attempt_count,
                'http_status' => $response->status(),
                'response_excerpt' => mb_substr($response->body(), 0, $excerptLength),
                'error' => null,
                'created_at' => now(),
            ]);

            $delivery->forceFill([
                'status' => $success ? 'delivered' : ($delivery->attempt_count >= $maxAttempts ? 'failed' : 'pending'),
                'delivered_at' => $success ? now() : null,
                'last_http_status' => $response->status(),
                'next_attempt_at' => $success || $delivery->attempt_count >= $maxAttempts
                    ? null
                    : now()->addSeconds((int) ($delay[min($delivery->attempt_count - 1, count($delay) - 1)] ?? 300)),
            ])->save();
        } catch (\Throwable $exception) {
            $delay = config('webhooks.retry_delay_seconds', [60, 300, 900, 3600]);
            $maxAttempts = (int) config('webhooks.max_attempts', 5);

            $delivery->attempts()->create([
                'attempt_number' => $delivery->attempt_count,
                'http_status' => null,
                'response_excerpt' => null,
                'error' => mb_substr($exception->getMessage(), 0, 2000),
                'created_at' => now(),
            ]);

            $delivery->forceFill([
                'status' => $delivery->attempt_count >= $maxAttempts ? 'failed' : 'pending',
                'next_attempt_at' => $delivery->attempt_count >= $maxAttempts
                    ? null
                    : now()->addSeconds((int) ($delay[min($delivery->attempt_count - 1, count($delay) - 1)] ?? 300)),
            ])->save();

            throw $exception;
        }
    }
}
