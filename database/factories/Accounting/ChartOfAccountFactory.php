<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(ChartOfAccount::class, function (Faker $faker) {
    return [
        'type_id' => factory(ChartOfAccountType::class),
        'is_sub_ledger' => $faker->numberBetween(0, 1),
        'position' => $faker->text,
        'is_locked' => $faker->numberBetween(0, 1),
        'name' => $faker->name,
        'alias' => $faker->name(),
    ];
});
