<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\KpiGroup;

$factory->define(KpiGroup::class, function (Faker $faker) {
    return [
        'kpi_id' => factory(\App\Model\HumanResource\Kpi\Kpi::class)->create()->id,
        'name' => $faker->name,
    ];
});
