<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiScore::class, function (Faker $faker) {
    return [
        'kpi_template_indicator_id' => factory(\App\Model\HumanResource\Kpi\KpiTemplateIndicator::class)->create()->id,
    ];
});
