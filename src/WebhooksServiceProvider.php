<?php

namespace ItmoSoft\Webhooks;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use ItmoSoft\Webhooks\Console\RetryWebhooksCommand;

class WebhooksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhooks.php', 'webhooks');
        $this->app->singleton('webhooks', fn ($app) => new Services\WebhookManager());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/webhooks.php' => config_path('webhooks.php'),
        ], 'webhooks-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'webhooks-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([RetryWebhooksCommand::class]);
        }
    }
}
