<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PapikostickCategorySeeder extends Seeder
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
                "name" => "G",
                "description" => "PEKERJA KERAS (Hard Intense Worked)",
                // "max" => 9,
                // "min" => 8
            ],
            [
                "id" => 2,
                "name" => "L",
                "description" => "MEMILIKI JIWA PEMIMPIN (Leadership Role)",
                // "max" => 9,
                // "min" => 7
            ],
            [
                "id" => 3,
                "name" => "I",
                "description" => "PERAN MEMBUAT KEPUTUSAN (Ease in Decision Making)",
                // "max" => 9,
                // "min" => 8
            ],
            [
                "id" => 4,
                "name" => "T",
                "description" => "BISA MEMBAGI WAKTU (Pace)",
                // "max" => 6,
                // "min" => 4
            ],
            [
                "id" => 5,
                "name" => "V",
                "description" => "PERAN MEMILIKI SEMANGAT (Vigorous Type)",
                // "max" => 9,
                // "min" => 7
            ],
            [
                "id" => 6,
                "name" => "S",
                "description" => "KEMAMPUAN MENJALIN HUBUNGAN SOSIAL (Social Extension)",
                // "max" => 9,
                // "min" => 6
            ],
            [
                "id" => 7,
                "name" => "R",
                "description" => "PERAN BEKERJA DI LAPANGAN (Theoretical Type)",
                // "max" => 9,
                // "min" => 5
            ],
            [
                "id" => 8,
                "name" => "D",
                "description" => "ORANG YANG SUKA BEKERJA DENGAN HAL – HAL RINCI (Interest in Working With Details)",
                // "max" => 9,
                // "min" => 4
            ],
            [
                "id" => 9,
                "name" => "C",
                "description" => "PERAN LEBIH MENGATUR (Organized Type)",
                // "max" => 7,
                // "min" => 3
            ],
            [
                "id" => 10,
                "name" => "E",
                "description" => "KEMAMPUAN MENGENDALIKAN EMOSI (Emotional Resistant)",
                // "max" => 6,
                // "min" => 4
            ],
            
            [
                "id" => 11,
                "name" => "N",
                "description" => "KEMAMPUAN MENYELESAIKAN TUGAS SECARA MANDIRI (Need to Finish Task)",
                // "max" => 9,
                // "min" => 6
            ],
            [
                "id" => 12,
                "name" => "A",
                "description" => "PERAN MEMILIKI AMBISI DALAM BERPRESTASI (Need to Achieve)",
                // "max" => 9,
                // "min" => 8
            ],
            [
                "id" => 13,
                "name" => "P",
                "description" => "KEMAMPUAN MENGATUR ORANG LAIN (Need to Control Others)",
                // "max" => 9,
                // "min" => 7
            ],
            [
                "id" => 14,
                "name" => "X",
                "description" => "KEBUTUHAN UNTUK DIPERHATIKAN (Need to be Noticed)",
                // "max" => 5,
                // "min" => 4
            ],
            [
                "id" => 15,
                "name" => "B",
                "description" => "INGIN DITERIMA DALAM KELOMPOK (Need to Belong to Groups)",
                // "max" => 5,
                // "min" => 4
            ],
            [
                "id" => 16,
                "name" => "O",
                "description" => "INGIN MEMILIKI KEDEKATAN DAN KASIH SAYANG (Need for Closeness and Affection)",
                // "max" => 4,
                // "min" => 3
            ],
            [
                "id" => 17,
                "name" => "Z",
                "description" => "PUNYA KEINGINAN UNTUK BERUBAH (Need for Change)",
                // "max" => 7,
                // "min" => 6
            ],
            [
                "id" => 18,
                "name" => "K",
                "description" => "KEAGRESIFAN DALAM PEKERJAAN (Need to be Forceful)",
                // "max" => 7,
                // "min" => 6
            ],
            [
                "id" => 19,
                "name" => "F",
                "description" => "KESETIAAN KEPADA ATASAN (Need to Support Authority)",
                // "max" => 7,
                // "min" => 4
            ],
            [
                "id" => 20,
                "name" => "W",
                "description" => "TIDAK BUTUH PENGAWASAN (Need for Rules and Supervision)",
                // "max" => 4,
                // "min" => 0
            ],
        ];
        foreach ($categories as $category) {
            DB::table('psychotest_papikostick_categories')->insert($category);
        }
    }
}
