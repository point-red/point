<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Master\FixedAssetGroup;
use Faker\Generator as Faker;

$factory->define(FixedAssetGroup::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
