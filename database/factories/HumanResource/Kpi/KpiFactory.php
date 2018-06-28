<?php

use Faker\Generator as Faker;
use App\Model\HumanResource\Kpi\Kpi;

$factory->define(Kpi::class, function (Faker $faker) {
    return [
        'employee_id' => factory(\App\Model\HumanResource\Employee\Employee::class)->create()->id,
        'date' => $faker->date(),
        'name' => $faker->name,
    ];
});
