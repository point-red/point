<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\KpiIndicator;

$factory->define(KpiIndicator::class, function (Faker $faker) {
    return [
        'kpi_group_id' => factory(\App\Model\HumanResource\Kpi\KpiGroup::class)->create()->id,
        'name' => $faker->name,
        'weight' => 5,
        'target' => 5,
        'score' => $faker->numberBetween($min = 1, $max = 5),
        'score_percentage' => $faker->numberBetween($min = 10, $max = 50),
    ];
});
