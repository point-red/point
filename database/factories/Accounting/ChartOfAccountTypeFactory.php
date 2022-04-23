<?php

use App\Model\Accounting\ChartOfAccountType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(ChartOfAccountType::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'alias' => $faker->name,
        'is_debit' => $faker->numberBetween(0, 1),
    ];
});
