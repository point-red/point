<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\HumanResource\Kpi\KpiTemplateIndicator::class, function (Faker $faker) {
    return [
        'kpi_template_group_id' => factory(\App\Model\HumanResource\Kpi\KpiTemplateGroup::class)->create()->id,
        'name' => $faker->name,
        'weight' => 20,
        'target' => 5,
    ];
});
