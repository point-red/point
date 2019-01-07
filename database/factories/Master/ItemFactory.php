<?php

use App\Model\Master\Item;
use Faker\Generator as Faker;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'chart_of_account_id' => null,
        'name' => $faker->name,
    ];
});
