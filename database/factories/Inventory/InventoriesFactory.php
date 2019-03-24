<?php

use Faker\Generator as Faker;

$factory->define(App\Model\Inventory\Inventory::class, function (Faker $faker) {
    return [
        'date' => '2019-3-21 05:05:05',
        'form_number' => function () {
            return factory(\App\Model\Form::class)->create()->number;
        },
        'warehouse_id' => $faker->numberBetween(1,5),
        'item_id' => $faker->numberBetween(1,10),
        'quantity' => $faker->numberBetween(1,10),
    ];
});
