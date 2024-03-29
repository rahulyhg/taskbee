<?php

namespace App\Providers;

use App\Billing\StripeSubscriptionGateway;
use App\Billing\SubscriptionGateway;
use App\Helpers\AuthorizationCodeGenerator;
use App\Helpers\InvitationCodeGenerator;
use App\Helpers\RandomNumberGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripeSubscriptionGateway::class, function () {
            return new StripeSubscriptionGateway(config('services.stripe.secret'));
        });

        $this->app->bind(SubscriptionGateway::class, StripeSubscriptionGateway::class);

        $this->app->bind(AuthorizationCodeGenerator::class, RandomNumberGenerator::class);
        $this->app->bind(InvitationCodeGenerator::class, RandomNumberGenerator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
