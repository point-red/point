<?php

use Faker\Generator as Faker;
use App\Model\Master\PersonGroup;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(PersonGroup::class, function (Faker $faker) {
    return [
        'code' => $faker->numberBetween($min = 1000, $max = 9999),
        'name' => $faker->name,
    ];
});
