<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Master\PricingGroup::class, function (Faker $faker) {
    return [
        'label' => $faker->name
    ];
});
