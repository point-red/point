<?php

use Faker\Generator as Faker;

$factory->define(\App\Model\Project\Project::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'code' => strtoupper(str_random(6)),
        'owner_id' => 1,
    ];
});
