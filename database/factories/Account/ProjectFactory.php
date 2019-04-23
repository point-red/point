<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(\App\Model\Project\Project::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'code' => strtoupper(Str::random(6)),
        'owner_id' => 1,
    ];
});
