<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Plugin\SalaryNonSales\FactorCriteria;
use App\Models\Plugin\SalaryNonSales\GroupFactor;
use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
    return [
        'level' => $faker->name,
        'description' => $faker->word,
        'score' => $faker->numberBetween(10, 99),
        'factorId' => GroupFactor::inRandomOrder()->first()->id,
    ];
});
