<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Plugin\SalaryNonSales\Group;
use App\Model\Plugin\SalaryNonSales\GroupFactor;
use Faker\Generator as Faker;

$factory->define(GroupFactor::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'group_id' => Group::inRandomOrder()->first()->id,
    ];
});
