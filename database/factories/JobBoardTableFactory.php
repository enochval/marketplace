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

use App\Models\JobBoard;
use Faker\Generator as Faker;

$factory->define(JobBoard::class, function (Faker $faker) {
    return [
        'employer_id' => 4,
        'title' => $faker->words(4, true),
        'description' => $faker->sentence(10, true),
        'duration' => $faker->randomDigitNotNull(1,20),
        'originating_amount' => $faker->randomDigitNotNull(10000,200000),
        'address' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->state,
    ];
});
