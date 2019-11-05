<?php

use App\Model\HumanResource\Kpi\Kpi;
use Faker\Generator as Faker;

$factory->define(Kpi::class, function (Faker $faker) {
    return [
        'employee_id' => factory(\App\Model\HumanResource\Employee\Employee::class)->create()->id,
        'date' => $faker->date(),
        'name' => $faker->name,
    ];
});
