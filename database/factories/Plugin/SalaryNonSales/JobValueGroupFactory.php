<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Plugin\SalaryNonSales\Group;
use Faker\Generator as Faker;

$factory->define(Group::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
