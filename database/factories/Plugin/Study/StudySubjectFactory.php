<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Plugin\Study\StudySubject;
use Faker\Generator as Faker;

$factory->define(StudySubject::class, function (Faker $faker) {
    return [
        'name' => ucfirst($faker->word()),
    ];
});
