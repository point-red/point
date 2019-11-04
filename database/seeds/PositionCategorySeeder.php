<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionCategorySeeder extends Seeder
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
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 8,
                "category_id" => 1
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 7,
                "category_id" => 2
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 8,
                "category_id" => 3
            ],
            [
                "position_id" => 1,
                "category_max" => 6,
                "category_min" => 4,
                "category_id" => 4
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 7,
                "category_id" => 5
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 6,
                "category_id" => 6
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 5,
                "category_id" => 7
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 4,
                "category_id" => 8
            ],
            [
                "position_id" => 1,
                "category_max" => 7,
                "category_min" => 3,
                "category_id" => 9
            ],
            [
                "position_id" => 1,
                "category_max" => 6,
                "category_min" => 4,
                "category_id" => 10
            ],
            
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 6,
                "category_id" => 11
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 8,
                "category_id" => 12
            ],
            [
                "position_id" => 1,
                "category_max" => 9,
                "category_min" => 7,
                "category_id" => 13
            ],
            [
                "position_id" => 1,
                "category_max" => 5,
                "category_min" => 4,
                "category_id" => 14
            ],
            [
                "position_id" => 1,
                "category_max" => 5,
                "category_min" => 4,
                "category_id" => 15
            ],
            [
                "position_id" => 1,
                "category_max" => 4,
                "category_min" => 3,
                "category_id" => 16
            ],
            [
                "position_id" => 1,
                "category_max" => 7,
                "category_min" => 6,
                "category_id" => 17
            ],
            [
                "position_id" => 1,
                "category_max" => 7,
                "category_min" => 6,
                "category_id" => 18
            ],
            [
                "position_id" => 1,
                "category_max" => 7,
                "category_min" => 4,
                "category_id" => 19
            ],
            [
                "position_id" => 1,
                "category_max" => 4,
                "category_min" => 1,
                "category_id" => 20
            ],
        ];
        foreach ($categories as $category) {
            DB::table('psychotest_position_categories')->insert($category);
        }
    }
}
