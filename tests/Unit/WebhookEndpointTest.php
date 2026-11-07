<?php

namespace ItmoSoft\Webhooks\Tests\Unit;

use ItmoSoft\Webhooks\Models\WebhookEndpoint;
use PHPUnit\Framework\TestCase;

class WebhookEndpointTest extends TestCase
{
    public function test_it_matches_an_explicitly_subscribed_event(): void
    {
        $endpoint = new WebhookEndpoint(['events' => ['order.created', 'order.paid']]);

        $this->assertTrue($endpoint->subscribesTo('order.created'));
        $this->assertFalse($endpoint->subscribesTo('invoice.paid'));
    }

    public function test_it_matches_all_events_when_wildcard_is_configured(): void
    {
        $endpoint = new WebhookEndpoint(['events' => ['*']]);

        $this->assertTrue($endpoint->subscribesTo('order.created'));
        $this->assertTrue($endpoint->subscribesTo('invoice.paid'));
    }

    public function test_it_does_not_match_events_when_subscription_list_is_empty(): void
    {
        $endpoint = new WebhookEndpoint(['events' => []]);

        $this->assertFalse($endpoint->subscribesTo('order.created'));
    }
}
