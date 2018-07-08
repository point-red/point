<?php

use App\Model\Master\Person;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Person::class, function (Faker $faker) {
    return [
        'code' => $faker->numberBetween($min = 10, $max = 999999),
        'name' => $faker->name,
        'notes' => $faker->text,
        'person_group_id' => function () {
            return factory(\App\Model\Master\PersonGroup::class)->create()->id;
        },
    ];
});
