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
            ["name" => "G", "description" => "PEKERJA KERAS (Hard Intense Worked)"],
            ["name" => "L", "description" => "MEMILIKI JIWA PEMIMPIN (Leadership Role)"],
            ["name" => "I", "description" => "PERAN MEMBUAT KEPUTUSAN (Ease in Decision Making)"],
            ["name" => "T", "description" => "BISA MEMBAGI WAKTU (Pace)"],
            ["name" => "V", "description" => "PERAN MEMILIKI SEMANGAT (Vigorous Type)"],
            ["name" => "S", "description" => "KEMAMPUAN MENJALIN HUBUNGAN SOSIAL (Social Extension)"],
            ["name" => "R", "description" => "PERAN BEKERJA DI LAPANGAN (Theoretical Type)"],
            ["name" => "D", "description" => "ORANG YANG SUKA BEKERJA DENGAN HAL – HAL RINCI (Interest in Working With Details)"],
            ["name" => "C", "description" => "PERAN LEBIH MENGATUR (Organized Type)"],
            ["name" => "E", "description" => "KEMAMPUAN MENGENDALIKAN EMOSI (Emotional Resistant)"],
            ["name" => "N", "description" => "KEMAMPUAN MENYELESAIKAN TUGAS SECARA MANDIRI (Need to Finish Task)"],
            ["name" => "A", "description" => "PERAN MEMILIKI AMBISI DALAM BERPRESTASI (Need to Achieve)"],
            ["name" => "P", "description" => "KEMAMPUAN MENGATUR ORANG LAIN (Need to Control Others)"],
            ["name" => "X", "description" => "KEBUTUHAN UNTUK DIPERHATIKAN (Need to be Noticed)"],
            ["name" => "B", "description" => "INGIN DITERIMA DALAM KELOMPOK (Need to Belong to Groups)"],
            ["name" => "O", "description" => "INGIN MEMILIKI KEDEKATAN DAN KASIH SAYANG (Need for Closeness and Affection)"],
            ["name" => "Z", "description" => "PUNYA KEINGINAN UNTUK BERUBAH (Need for Change)"],
            ["name" => "K", "description" => "KEAGRESIFAN DALAM PEKERJAAN (Need to be Forceful)"],
            ["name" => "F", "description" => "KESETIAAN KEPADA ATASAN (Need to Support Authority)"],
            ["name" => "W", "description" => "TIDAK BUTUH PENGAWASAN (Need for Rules and Supervision)"],
        ];
        foreach ($categories as $category) {
            DB::table('psychotest_papikostick_categories')->insert($category);
        }
    }
}
