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
                "name" => "G",
                "description" => "PEKERJA KERAS (Hard Intense Worked)",
                "max" => 9,
                "min" => 8
            ],
            [
                "name" => "L",
                "description" => "MEMILIKI JIWA PEMIMPIN (Leadership Role)",
                "max" => 9,
                "min" => 7
            ],
            [
                "name" => "I",
                "description" => "PERAN MEMBUAT KEPUTUSAN (Ease in Decision Making)",
                "max" => 9,
                "min" => 8
            ],
            [
                "name" => "T",
                "description" => "BISA MEMBAGI WAKTU (Pace)",
                "max" => 6,
                "min" => 4
            ],
            [
                "name" => "V",
                "description" => "PERAN MEMILIKI SEMANGAT (Vigorous Type)",
                "max" => 9,
                "min" => 7
            ],
            [
                "name" => "S",
                "description" => "KEMAMPUAN MENJALIN HUBUNGAN SOSIAL (Social Extension)",
                "max" => 9,
                "min" => 6
            ],
            [
                "name" => "R",
                "description" => "PERAN BEKERJA DI LAPANGAN (Theoretical Type)",
                "max" => 9,
                "min" => 5
            ],
            [
                "name" => "D",
                "description" => "ORANG YANG SUKA BEKERJA DENGAN HAL – HAL RINCI (Interest in Working With Details)",
                "max" => 9,
                "min" => 4
            ],
            [
                "name" => "C",
                "description" => "PERAN LEBIH MENGATUR (Organized Type)",
                "max" => 7,
                "min" => 3
            ],
            [
                "name" => "E",
                "description" => "KEMAMPUAN MENGENDALIKAN EMOSI (Emotional Resistant)",
                "max" => 6,
                "min" => 4
            ],
            
            [
                "name" => "N",
                "description" => "KEMAMPUAN MENYELESAIKAN TUGAS SECARA MANDIRI (Need to Finish Task)",
                "max" => 9,
                "min" => 6
            ],
            [
                "name" => "A",
                "description" => "PERAN MEMILIKI AMBISI DALAM BERPRESTASI (Need to Achieve)",
                "max" => 9,
                "min" => 8
            ],
            [
                "name" => "P",
                "description" => "KEMAMPUAN MENGATUR ORANG LAIN (Need to Control Others)",
                "max" => 9,
                "min" => 7
            ],
            [
                "name" => "X",
                "description" => "KEBUTUHAN UNTUK DIPERHATIKAN (Need to be Noticed)",
                "max" => 5,
                "min" => 4
            ],
            [
                "name" => "B",
                "description" => "INGIN DITERIMA DALAM KELOMPOK (Need to Belong to Groups)",
                "max" => 5,
                "min" => 4
            ],
            [
                "name" => "O",
                "description" => "INGIN MEMILIKI KEDEKATAN DAN KASIH SAYANG (Need for Closeness and Affection)",
                "max" => 4,
                "min" => 3
            ],
            [
                "name" => "Z",
                "description" => "PUNYA KEINGINAN UNTUK BERUBAH (Need for Change)",
                "max" => 7,
                "min" => 6
            ],
            [
                "name" => "K",
                "description" => "KEAGRESIFAN DALAM PEKERJAAN (Need to be Forceful)",
                "max" => 7,
                "min" => 6
            ],
            [
                "name" => "F",
                "description" => "KESETIAAN KEPADA ATASAN (Need to Support Authority)",
                "max" => 7,
                "min" => 4
            ],
            [
                "name" => "W",
                "description" => "TIDAK BUTUH PENGAWASAN (Need for Rules and Supervision)",
                "max" => 4,
                "min" => 0
            ],
        ];
        foreach ($categories as $category) {
            DB::table('psychotest_papikostick_categories')->insert($category);
        }
    }
}
