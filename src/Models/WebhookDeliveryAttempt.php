<?php

namespace ItmoSoft\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDeliveryAttempt extends Model
{
    protected $guarded = [];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(WebhookDelivery::class, 'delivery_id');
    }
}
