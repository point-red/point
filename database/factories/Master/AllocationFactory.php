<?php

use App\Model\Master\Allocation;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Allocation::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
