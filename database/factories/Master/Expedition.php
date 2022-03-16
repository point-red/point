<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Master\Expedition::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'disabled' => 0
    ];
});
