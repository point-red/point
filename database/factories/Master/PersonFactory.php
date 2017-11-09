<?php

use App\Model\Master\Person;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Person::class, function (Faker $faker) {
    return [
        'code' => $faker->numberBetween($min = 1000, $max = 9999) ,
        'name' => $faker->name,
        'phone' => $faker->phoneNumber,
        'email' => $faker->email,
        'address' => $faker->address,
        'notes' => $faker->text,
    ];
});
