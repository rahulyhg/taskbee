<?php

namespace Feature;

use App\Billing\FakeSubscriptionGateway;
use App\Billing\SubscriptionGateway;
use App\Facades\AuthorizationCode;
use App\Mail\SubscriptionPurchasedEmail;
use App\Models\Bundle;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WorkspaceSetupAuthorization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionGateway = new FakeSubscriptionGateway;
        $this->app->instance(SubscriptionGateway::class, $this->subscriptionGateway);
        $this->plan = factory(Plan::class)->create([
            'amount' => 2500,
            'product' => factory(Bundle::class)->create()->stripe_id,
        ]);
    }

    /** @test */
    function guests_cannot_purchase_a_bundle_subscription()
    {
        $response = $this->json('POST', "bundles/{$this->plan->id}/checkout", [])->assertStatus(401);
    }

    /** @test */
    function authenticated_users_can_are_subscribe_to_a_bundle_with_successful_purchase()
    {
        // Work in progress
        $this->withoutExceptionHandling();
        Mail::fake();
        AuthorizationCode::shouldReceive('generate')->andReturn('TESTCODE123');

        $user = factory(User::class)->create();

        $plan = $this->SetupStripe();

        $response = $this->actingAs($user)->json('POST', "/bundles/{$plan->id}/checkout", [
            'email' => 'jane@example.com',
            'tok' => $tok,
        ]);
        
        dd($response);
        $subscription = $plan->subscriptions()->where('email', 'jane@example.com')->first();
        $this->assertNotNull($subscription);
        $this->assertEquals(2500, $subscription->amount);
        $this->assertEquals($subscription->expires_at, Carbon::now()->addMonth());

        $this->assertCount(1, WorkspaceSetupAuthorization::all());
        $setupAuthorization = WorkspaceSetupAuthorization::first();
        $this->assertNotNull($setupAuthorization);
        $this->assertEquals('jane@example.com', $setupAuthorization->email);
        $this->assertEquals('TESTCODE123', $setupAuthorization->code);

        Mail::assertQueued(SubscriptionPurchasedEmail::class, function($mail) use ($subscription, $setupAuthorization) {
            return $mail->hasTo('jane@example.com')
                && $mail->setupAuthorization->is($setupAuthorization)
                && $mail->subscription->id == $subscription->id;
        });
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

    private function setupStripe()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $basicBundle = \Stripe\Product::create([
            "name" => 'Testing Workspace Bundle',
            "type" => "service",
            "metadata" => [
                "members_limit" => 5,
            ],
        ]);

        $basicPlan = \Stripe\Plan::create([
            "amount" => 3995,
            "interval" => "month",
            "product" => $basicBundle['id'],
            "currency" => "eur",
        ]);

        return Plan::create([
            "name" => $basicPlan['nickname'],
            "amount" => $basicPlan['amount'],
            "interval" => "month",
            "product" => $basicBundle['id'],
            "currency" => "eur",
            "stripe_id" => $basicPlan['id'],
        ]);
    }
}
