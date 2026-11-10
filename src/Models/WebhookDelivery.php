<?php

namespace ItmoSoft\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'next_attempt_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('webhooks.table_prefix', 'itmosoft_').'webhook_deliveries';
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'endpoint_id');
    }

    public function attempts()
    {
        return $this->hasMany(WebhookDeliveryAttempt::class, 'delivery_id');
    }
}
