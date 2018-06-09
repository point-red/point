<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\KpiGroup;

$factory->define(KpiGroup::class, function (Faker $faker) {
    return [
        'kpi_category_id' => factory(\App\Model\HumanResource\Kpi\KpiCategory::class)->create()->id,
        'name' => $faker->name,
    ];
});
