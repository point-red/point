<?php

use Faker\Generator as Faker;

$factory->define(App\Model\Inventory\Inventory::class, function (Faker $faker) {
    return [
        'form_id' => function () {
            return factory(\App\Model\Form::class)->create()->id;
        },
        'warehouse_id' => $faker->numberBetween(1,3),
        'item_id' => $faker->numberBetween(1,10),
        'quantity' => $faker->numberBetween(1,10),
    ];
});
