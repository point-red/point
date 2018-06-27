<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiTemplateScore::class, function (Faker $faker) {
    return [
        'kpi_template_indicator_id' => factory(\App\Model\HumanResource\Kpi\KpiTemplateIndicator::class)->create()->id,
        'description' => $faker->text,
        'score' => 1,
    ];
});
