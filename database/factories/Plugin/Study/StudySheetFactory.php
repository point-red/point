<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Plugin\Study\StudySheet;
use App\Model\Plugin\Study\StudySubject;
use Faker\Generator as Faker;

$factory->define(StudySheet::class, function (Faker $faker) {
    return [
        'started_at' => now()->startOfHour(),
        'ended_at' => function (array $attributes) {
            return Carbon\Carbon::make($attributes['started_at'])->addHour();
        },
        'institution' => $faker->text(),
        'teacher' => $faker->name(),
        'competency' => $faker->text(),
        'learning_goals' => $faker->text(),
        'activities' => $faker->text(),
        'grade' => $faker->numberBetween(0,100),
        'behavior' => $faker->randomElement(['A', 'B', 'C']),
        'remarks' => $faker->text(),
        'subject_id' => factory(StudySubject::class),
    ];
});
