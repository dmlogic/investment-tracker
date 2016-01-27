<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'password' => password_hash(str_random(10),PASSWORD_DEFAULT),
    ];
});

$factory->define(App\Models\Group::class, function (Faker\Generator $faker) {
    return [];
});

$factory->define(App\Models\Fund::class, function (Faker\Generator $faker) {
    return [
        'type'        => 'fund',
        'currency'    => 'GBP',
        'price_units' => 100,
    ];
});
