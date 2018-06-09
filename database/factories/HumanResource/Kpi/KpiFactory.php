<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\Kpi;

$factory->define(Kpi::class, function (Faker $faker) {
    return [
        'kpi_group_id' => factory(\App\Model\HumanResource\Kpi\KpiGroup::class)->create()->id,
        'indicator' => $faker->name,
        'weight' => 5,
        'target' => 5,
        'score' => $faker->numberBetween($min = 1, $max = 5),
        'score_percentage' => $faker->numberBetween($min = 10, $max = 50),
    ];
});
