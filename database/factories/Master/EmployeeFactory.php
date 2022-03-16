<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Employee\Employee::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'daily_transport_allowance' => $faker->numberBetween(10, 999999),
        'functional_allowance' => $faker->numberBetween(10, 999999),
        'communication_allowance' => $faker->numberBetween(10, 999999),
    ];
});
