<?php

use App\Model\Master\Branch;
use App\Model\Master\Warehouse;
use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Warehouse::class, function (Faker $faker) {
    return [
        'code' => $faker->postcode,
        'name' => $faker->name,
        'address' => $faker->address,
        'phone' => $faker->e164PhoneNumber,
        'notes' => $faker->text(),
        'branch_id' => factory(Branch::class),
    ];
});
