<?php

return [
    'table_prefix' => env('ITMOSOFT_WEBHOOKS_TABLE_PREFIX', 'itmosoft_'),
    'timeout' => 10,
    'max_attempts' => 5,
    'retry_delay_seconds' => [60, 300, 900, 3600],
    'response_excerpt_length' => 2000,
];
