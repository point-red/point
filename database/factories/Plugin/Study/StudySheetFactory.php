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
        'institution' => $faker->domainName(),
        'teacher' => $faker->name(),
        'competency' => $faker->text(20),
        'learning_goals' => $faker->text(20),
        'activities' => $faker->text(10),
        'grade' => $faker->numberBetween(0,20) * 5,
        'behavior' => $faker->randomElement(['A', 'B', 'C']),
        'remarks' => $faker->text(20),
        'subject_id' => factory(StudySubject::class),
        'is_draft' => false,
    ];
});
