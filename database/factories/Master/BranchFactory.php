<?php

use App\Model\Master\Branch;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Branch::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
    ];
});
