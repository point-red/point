<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidatePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                "id" => 1,
                "position" => "HR",
            ]
        ];
        foreach ($categories as $category) {
            DB::table('psychotest_candidate_positions')->insert($category);
        }
    }
}
