<?php

namespace ItmoSoft\Webhooks\Tests;

use ItmoSoft\Webhooks\WebhooksServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [WebhooksServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('webhooks.table_prefix', 'itmosoft_');
        $app['config']->set('queue.default', 'sync');
    }
}
