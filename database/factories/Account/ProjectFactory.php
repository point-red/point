<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\App\Model\Project\Project::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'code' => strtoupper(Str::random(6)),
        'owner_id' => 1,
    ];
});
