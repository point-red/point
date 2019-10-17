<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model\Psychotest\PapikostickQuestion;
use Faker\Generator as Faker;

$factory->define(PapikostickQuestion::class, function (Faker $faker) {
    static $number = 1;
    return [
        'number' => $number++
    ];
});
