<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiTemplate::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
