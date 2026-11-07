<?php

namespace ItmoSoft\Webhooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string dispatch(string $event, array $payload): string
 */
class Webhooks extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'webhooks';
    }
}
