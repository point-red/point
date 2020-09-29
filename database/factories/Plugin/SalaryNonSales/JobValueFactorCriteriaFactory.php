<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Plugin\SalaryNonSales\FactorCriteria;
use App\Model\Plugin\SalaryNonSales\GroupFactor;
use Faker\Generator as Faker;

$factory->define(FactorCriteria::class, function (Faker $faker) {
    return [
        'level' => $faker->name,
        'description' => $faker->word,
        'score' => $faker->numberBetween(10, 99),
        'factor_id' => GroupFactor::inRandomOrder()->first()->id,
    ];
});
