<?php

namespace Feature;

use App\Billing\FakeSubscriptionGateway;
use App\Billing\SubscriptionGateway;
use App\Models\Bundle;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionGateway = new FakeSubscriptionGateway;
        $this->app->instance(SubscriptionGateway::class, $this->subscriptionGateway);
        $this->plan = factory(Plan::class)->create(['amount' => 2500]);
    }

    /** @test */
    function customer_can_subscribe_to_a_bundle_with_valid_token()
    {
        $response = $this->json('POST', "/bundles/{$this->plan->id}/purchase", [
            'email' => 'jane@example.com',
            'payment_token' => $this->subscriptionGateway->getValidTestToken(),
        ]);
        
        $response->assertStatus(201);
        $subscription = $this->plan->subscriptions()->where('email', 'jane@example.com')->first();
        $this->assertNotNull($subscription);
        $this->assertEquals(2500, $subscription->amount);
        $this->assertEquals($subscription->expires_at, Carbon::now()->addMonth());
    }

    /** @test */
    function subscription_is_not_created_if_payment_fails()
    {
        $response = $this->json('POST', "/bundles/{$this->plan->id}/purchase", [
            'email' => 'jane@example.com',
            'token' => "invalid-token",
        ]);
        
        $response->assertStatus(422);
        $this->assertCount(0, Customer::all());
        $this->assertCount(0, Subscription::all());
    }

    /** @test */
    function email_is_required_to_purchase_a_subscription()
    {
        $response = $this->json('POST', "/bundles/{$this->plan->id}/purchase", [
            'token' => $this->subscriptionGateway->getValidTestToken(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
        $this->assertCount(0, Customer::all());
        $this->assertCount(0, Subscription::all());
    }

    /** @test */
    function token_is_required_to_purchase_a_subscription()
    {
        $response = $this->json('POST', "/bundles/{$this->plan->id}/purchase", [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_token');
        $this->assertCount(0, Customer::all());
        $this->assertCount(0, Subscription::all());
    }
}