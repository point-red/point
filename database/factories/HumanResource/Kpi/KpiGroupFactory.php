<?php

use App\Model\HumanResource\Kpi\KpiGroup;
use Faker\Generator as Faker;

$factory->define(KpiGroup::class, function (Faker $faker) {
    return [
        'kpi_id' => factory(\App\Model\HumanResource\Kpi\Kpi::class)->create()->id,
        'name' => $faker->name,
    ];
});
