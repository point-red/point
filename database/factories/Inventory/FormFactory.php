<?php

use Faker\Generator as Faker;

$factory->define(App\Model\Form::class, function (Faker $faker) {
    return [
        'date' => '2019-3-20 05:05:05',
        'number' => $faker->numerify('form-#####'),
        'formable_id' => $faker->numberBetween(1, 20),
        'formable_type' => 'transfer',
        'created_by' => 1,
        'updated_by' => 1,
    ];
});
