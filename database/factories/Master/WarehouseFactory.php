<?php

use App\Model\Master\Warehouse;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Warehouse::class, function (Faker $faker) {
    return [
        'code' => $faker->numberBetween($min = 10, $max = 999999),
        'name' => $faker->name,
    ];
});
