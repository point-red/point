<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Master\ItemUnit::class, function (Faker $faker) {
    $units = ['pcs', 'box', 'kg'];
    return [
        'label' => $units[rand(0, 2)],
        'name' => $units[rand(0, 2)],
    ];
});
