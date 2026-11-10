<?php

namespace ItmoSoft\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDeliveryAttempt extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function getTable(): string
    {
        return config('webhooks.table_prefix', 'itmosoft_').'webhook_delivery_attempts';
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(WebhookDelivery::class, 'delivery_id');
    }
}
