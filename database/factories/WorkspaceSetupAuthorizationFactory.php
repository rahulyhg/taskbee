<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\WorkspaceSetupAuthorization;
use Faker\Generator as Faker;

$factory->define(WorkspaceSetupAuthorization::class, function (Faker $faker) {
    return [
        'email' => $faker->email,
		'user_role' => App\Models\User::ADMIN,
		'code' => 'AUTHORIZATIONCODE1234',
		'subscription_id' => function () {
			return factory(App\Models\Subscription::class)->create()->id;
		},
		'plan_id' => function () {
			return factory(App\Models\Plan::class)->create()->stripe_id;
		},
    ];
});