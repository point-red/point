<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Master\FixedAsset;
use Faker\Generator as Faker;

$factory->define(FixedAsset::class, function (Faker $faker) {
    return [
        'code' => "".$faker->numberBetween(10, 999999),
        'name' => $faker->name,
        'depreciation_method' => FixedAsset::$DEPRECIATION_METHOD_STRAIGHT_LINE
    ];
});
