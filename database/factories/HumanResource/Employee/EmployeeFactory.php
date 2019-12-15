<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Employee\Employee::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
