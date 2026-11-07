<?php

namespace ItmoSoft\Webhooks\Console;

use Illuminate\Console\Command;
use ItmoSoft\Webhooks\Jobs\DeliverWebhookJob;
use ItmoSoft\Webhooks\Models\WebhookDelivery;

class RetryWebhooksCommand extends Command
{
    protected $signature = 'webhooks:retry {--limit=100}';
    protected $description = 'Queue due outbound webhook deliveries for another attempt';

    public function handle(): int
    {
        $count = 0;

        WebhookDelivery::query()
            ->where('status', 'pending')
            ->whereNotNull('next_attempt_at')
            ->where('next_attempt_at', '<=', now())
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get()
            ->each(function (WebhookDelivery $delivery) use (&$count) {
                DeliverWebhookJob::dispatch($delivery->getKey());
                $count++;
            });

        $this->info("Queued {$count} webhook delivery(s).");

        return self::SUCCESS;
    }
}
