<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiResult::class, function (Faker $faker) {
    return [
        'score_min' => 0,
        'score_max' => 20,
        'criteria' => $faker->text,
        'notes' => $faker->text,
    ];
});
