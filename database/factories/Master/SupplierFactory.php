<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Master\Supplier::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
