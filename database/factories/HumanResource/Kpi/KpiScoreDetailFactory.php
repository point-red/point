<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiScoreDetail::class, function (Faker $faker) {
    return [
        'kpi_score_id' => factory(\App\Model\HumanResource\Kpi\KpiScore::class)->create()->id,
        'description' => $faker->text,
        'score' => 1,
    ];
});
