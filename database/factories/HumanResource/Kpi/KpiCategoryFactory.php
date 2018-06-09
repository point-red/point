<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\KpiCategory;

$factory->define(KpiCategory::class, function (Faker $faker) {
    return [
        'person_id' => factory(App\Model\Master\Person::class)->create()->id,
        'date' => $faker->date(),
        'name' => $faker->name,
    ];
});
