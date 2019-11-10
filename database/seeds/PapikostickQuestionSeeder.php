<?php

use App\Model\Psychotest\PapikostickQuestion;
use Illuminate\Database\Seeder;

class PapikostickQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(PapikostickQuestion::class, 90)->create();
    }
}
