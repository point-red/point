<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Master\Customer::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
