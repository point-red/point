<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiTemplateGroup::class, function (Faker $faker) {
    return [
        'kpi_template_id' => factory(\App\Model\HumanResource\Kpi\KpiTemplate::class)->create()->id,
        'name' => $faker->name
    ];
});
