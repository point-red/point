<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PapikostickOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            ["category_id" => 1, "question_id" => 1, "content" => "Saya seorang pekerja keras"],
            ["category_id" => 10, "question_id" => 1, "content" => "Saya bukan seorang pemurung"],

            ["category_id" => 12, "question_id" => 2, "content" => "Saya suka bekerja lebih baik dari orang lain"],
            ["category_id" => 11, "question_id" => 2, "content" => "Saya suka mengerjakan apa yang tengah saya kerjakan, hingga selesai"],

            ["category_id" => 13, "question_id" => 3, "content" => "Saya suka menawarkan triknya melaksanakan sesuatu hal"],
            ["category_id" => 12, "question_id" => 3, "content" => "Saya ingin bekerja sebaik mungkin"],

            ["category_id" => 14, "question_id" => 4, "content" => "Saya suka berkelakar"],
            ["category_id" => 13, "question_id" => 4, "content" => "Saya bahagia menyampaikan kepada orang lain, apa yang harus dilakukannya"],

            ["category_id" => 15, "question_id" => 5, "content" => "Saya suka menggabungkan diri dengan kelompok-kelompok"],
            ["category_id" => 14, "question_id" => 5, "content" => "Saya suka diperhatikan oleh kelompok-kelompok"],

            ["category_id" => 16, "question_id" => 6, "content" => "Saya bahagia akrab intim dengan seseorang"],
            ["category_id" => 15, "question_id" => 6, "content" => "Saya bahagia akrab dengan sekelompok orang"],

            ["category_id" => 17, "question_id" => 7, "content" => "Saya cepat berubah bila hal itu dibutuhkan"],
            ["category_id" => 16, "question_id" => 7, "content" => "Saya berusaha untuk intim dengan teman-teman"],

            ["category_id" => 18, "question_id" => 8, "content" => "Saya suka membalas dendam bila saya benar-benar disakiti"],
            ["category_id" => 17, "question_id" => 8, "content" => "Saya suka melaksanakan hal-hal yang gres dan berbeda"],

            ["category_id" => 19, "question_id" => 9, "content" => "Saya ingin atasan saya menyukai saya"],
            ["category_id" => 18, "question_id" => 9, "content" => "Saya suka menyampaikan kepada orang lain, bila mereka salah"],

            ["category_id" => 20, "question_id" => 10, "content" => "Saya suka mengikuti perintah-perintah yang diberikan kepada saya"],
            ["category_id" => 19, "question_id" => 10, "content" => "Saya suka menyenangkan hati orang yang memimpin saya"],

            ["category_id" => 1, "question_id" => 11, "content" => "Saya mencoba sekuat tenaga"],
            ["category_id" => 9, "question_id" => 11, "content" => "Saya seorang yang tertib."],

            ["category_id" => 2, "question_id" => 12, "content" => "Saya menciptakan orang lain melaksanakan apa yang saya inginkan"],
            ["category_id" => 10, "question_id" => 12, "content" => "Saya bukan seorang yang cepat gusar"],

            ["category_id" => 13, "question_id" => 13, "content" => "Saya suka menyampaikan kepada kelompok, apa yang harus dilakukan"],
            ["category_id" => 11, "question_id" => 13, "content" => "Saya menekuni satu pekerjaan hingga selesai"],

            ["category_id" => 14, "question_id" => 14, "content" => "Saya ingin tampak bersemangat dan menarik"],
            ["category_id" => 12, "question_id" => 14, "content" => "Saya ingin menjadi sangat sukses"],

            ["category_id" => 15, "question_id" => 15, "content" => "Saya suka menyelaraskan diri dengan kelompok"],
            ["category_id" => 13, "question_id" => 15, "content" => "Saya suka membantu orang lain memilih pendapatnya"],

            ["category_id" => 16, "question_id" => 16, "content" => "Saya cemas kalau orang lain tidak menyukai saya"],
            ["category_id" => 14, "question_id" => 16, "content" => "Saya bahagia kalau orang-orang memperhatikan saya"],

            ["category_id" => 17, "question_id" => 17, "content" => "Saya suka mencoba sesuatu yang baru"],
            ["category_id" => 15, "question_id" => 17, "content" => "Saya lebih suka bekerja bersama orang-orang daripada bekerja sendiri"],

            ["category_id" => 18, "question_id" => 18, "content" => "Kadang-kadang saya menyalahkan orang lain bila tejadi sesuatu kesalahan"],
            ["category_id" => 16, "question_id" => 18, "content" => "Saya cemas bila seseorang tidak menyukai saya"],

            ["category_id" => 19, "question_id" => 19, "content" => "Saya suka menyenangkan hati orang yang memimpin saya"],
            ["category_id" => 17, "question_id" => 19, "content" => "Saya suka mencoba pekerjaan-pekerjaan yang gres dan berbeda"],

            ["category_id" => 20, "question_id" => 20, "content" => "Saya menyukai petunjuk yang terinci untuk melaksanakan sesuatu pekerjaan"],
            ["category_id" => 18, "question_id" => 20, "content" => "Saya suka menyampaikan kepada orang lain bila menyesatkan saya"],

            ["category_id" => 1, "question_id" => 21, "content" => "Saya selalu mencoba sekuat tenaga"],
            ["category_id" => 8, "question_id" => 21, "content" => "Saya bahagia bekerja dengan sangat cermat dan hati-hati"],

            ["category_id" => 2, "question_id" => 22, "content" => "Saya yakni seorang pemimpin yang baik"],
            ["category_id" => 9, "question_id" => 22, "content" => "Saya mengorganisir tugas-tugas setrik baik"],

            ["category_id" => 3, "question_id" => 23, "content" => "Saya ringannya menjadi gusar"],
            ["category_id" => 10, "question_id" => 23, "content" => "Saya seorang yang lambat dalam menciptakan keputusan"],

            ["category_id" => 14, "question_id" => 24, "content" => "Saya bahagia mengerjakan beberapa pekerjaan pada waktu yang bersamaan"],
            ["category_id" => 11, "question_id" => 24, "content" => "Bila di dalam kelompok, saya lebih suka diam"],

            ["category_id" => 15, "question_id" => 25, "content" => "Saya bahagia bila diundang"],
            ["category_id" => 12, "question_id" => 25, "content" => "Saya ingin melaksanakan sesuatu lebih baik dari orang lain"],

            ["category_id" => 16, "question_id" => 26, "content" => "Saya suka berteman intim dengan teman-teman saya"],
            ["category_id" => 13, "question_id" => 26, "content" => "Saya suka memberi pesan yang tersirat kepada orang lain"],

            ["category_id" => 17, "question_id" => 27, "content" => "Saya suka melaksanakan hal-hal yang gres dan berbeda"],
            ["category_id" => 14, "question_id" => 27, "content" => "Saya suka menceritakan keberhasilan saya dalam mengerjakan tugas"],

            ["category_id" => 18, "question_id" => 28, "content" => "Bila saya benar, saya suka mempertahankannya mati-matian"],
            ["category_id" => 15, "question_id" => 28, "content" => "Saya suka bergabung ke dalam suatu kelompok"],

            ["category_id" => 19, "question_id" => 29, "content" => "Saya tidak mau berbeda dengan orang lain"],
            ["category_id" => 16, "question_id" => 29, "content" => "Saya berusaha untuk sangat intim dengan orang-orang"],

            ["category_id" => 20, "question_id" => 30, "content" => "Saya suka diajari mengenai triknya mengerjakan suatu pekerjaan"],
            ["category_id" => 17, "question_id" => 30, "content" => "Saya ringannya merasa jemu (bosan)"],

            ["category_id" => 1, "question_id" => 31, "content" => "Saya bekerja keras"],
            ["category_id" => 7, "question_id" => 31, "content" => "Saya banyak berpikir dan berencana"],

            ["category_id" => 2, "question_id" => 32, "content" => "Saya memimpin kelompok"],
            ["category_id" => 8, "question_id" => 32, "content" => "Hal-hal yang kecil (detail) menggoda saya"],

            ["category_id" => 3, "question_id" => 33, "content" => "Saya cepat dan ringannya mengambil keputusan"],
            ["category_id" => 9, "question_id" => 33, "content" => "Saya melaksanakan segala sesuatu setrik rapih dan teratur"],

            ["category_id" => 4, "question_id" => 34, "content" => "Tugas-tugas saya kerjakan setrik cepat"],
            ["category_id" => 10, "question_id" => 34, "content" => "Saya jarang murka atau sedih"],

            ["category_id" => 15, "question_id" => 35, "content" => "Saya ingin menjadi bab dari kelompok"],
            ["category_id" => 11, "question_id" => 35, "content" => "Pada suatu waktu tertentu, saya hanya ingin mengerjakan satu kiprah saja"],

            ["category_id" => 16, "question_id" => 36, "content" => "Saya berusaha untuk intim dengan teman-teman saya"],
            ["category_id" => 12, "question_id" => 36, "content" => "Saya berusaha keras untuk menjadi yang terbaik"],

            ["category_id" => 17, "question_id" => 37, "content" => "Saya menyukai mode baju gres dan tipe-tipe kendaraan beroda empat baru"],
            ["category_id" => 13, "question_id" => 37, "content" => "Saya ingin menjadi penanggung jawab bagi orang-orang lain"],

            ["category_id" => 18, "question_id" => 38, "content" => "Saya suka berdebat"],
            ["category_id" => 14, "question_id" => 38, "content" => "Saya ingin diperhatikan"],

            ["category_id" => 19, "question_id" => 39, "content" => "Saya suka menyenangkan hati orang yang memimpin saya"],
            ["category_id" => 15, "question_id" => 39, "content" => "Saya tertarik menjadi anggota dari suatu kelompok"],

            ["category_id" => 20, "question_id" => 40, "content" => "Saya bahagia mengikuti hukum setrik tertib"],
            ["category_id" => 16, "question_id" => 40, "content" => "Saya suka orang-orang mengenal saya benar-benar"],

            ["category_id" => 1, "question_id" => 41, "content" => "Saya mencoba sekuat tenaga"],
            ["category_id" => 6, "question_id" => 41, "content" => "Saya sangat menyenangkan"],

            ["category_id" => 2, "question_id" => 42, "content" => "Orang lain beranggapan bahwa saya yakni seorang pemimpin yang baik"],
            ["category_id" => 7, "question_id" => 42, "content" => "Saya berpikir jauh ke depan dan terinci"],

            ["category_id" => 3, "question_id" => 43, "content" => "Seringkali saya memanfaatkan peluang"],
            ["category_id" => 8, "question_id" => 43, "content" => "Saya bahagia memperhatikan hal-hal hingga sekecil-kecilnya"],

            ["category_id" => 4, "question_id" => 44, "content" => "Orang lain menganggap saya bekerja cepat"],
            ["category_id" => 9, "question_id" => 44, "content" => "Orang lain menganggap saya sanggup melaksanakan penataan yang rapi dan teratur"],

            ["category_id" => 5, "question_id" => 45, "content" => "Saya menyukai permainan-permainan dan olahraga"],
            ["category_id" => 10, "question_id" => 45, "content" => "Saya sangat menyenangkan"],

            ["category_id" => 16, "question_id" => 46, "content" => "Saya bahagia bila orang-orang sanggup intim dan bersahabat"],
            ["category_id" => 11, "question_id" => 46, "content" => "Saya selalu berusaha menuntaskan apa yang telah saya mulai"],

            ["category_id" => 17, "question_id" => 47, "content" => "Saya suka bereksperimen dan mencoba sesuatu yang baru"],
            ["category_id" => 12, "question_id" => 47, "content" => "Saya suka mengerjakan pekerjaan-pekerjaan yang sulit dengan baik"],

            ["category_id" => 18, "question_id" => 48, "content" => "Saya bahagia diperlakukan setrik adil"],
            ["category_id" => 13, "question_id" => 48, "content" => "Saya bahagia mengajari orang lain bagaimana triknya mengerjakan sesuatu"],

            ["category_id" => 19, "question_id" => 49, "content" => "Saya suka mengerjakan apa yang diperlukan dari saya"],
            ["category_id" => 14, "question_id" => 49, "content" => "Saya suka menarik perhatian"],

            ["category_id" => 20, "question_id" => 50, "content" => "Saya suka petunjuk-petunjuk terinci dalam melaksanakan sesuatu pekerjaan"],
            ["category_id" => 15, "question_id" => 50, "content" => "Saya bahagia berada bersama dengan orang-orang lain"],

            ["category_id" => 1, "question_id" => 51, "content" => "Saya selalu berusaha mengerjakan kiprah setrik sempurna"],
            ["category_id" => 5, "question_id" => 51, "content" => "Orang lain menganggap, saya tidak mengenal lelah, dalam kerja sehari-hari"],

            ["category_id" => 2, "question_id" => 52, "content" => "Saya tergolong tipe pemimpin"],
            ["category_id" => 6, "question_id" => 52, "content" => "Saya ringannya berteman"],

            ["category_id" => 3, "question_id" => 53, "content" => "Saya memanfaatkan peluang-peluang"],
            ["category_id" => 7, "question_id" => 53, "content" => "Saya banyak berfikir"],

            ["category_id" => 4, "question_id" => 54, "content" => "Saya bekerja dengan kecepatan yang mantap dan cepat"],
            ["category_id" => 8, "question_id" => 54, "content" => "Saya bahagia mengerjakan hal-hal yang detail"],

            ["category_id" => 5, "question_id" => 55, "content" => "Saya memliki banyak energi untuk permainan-permainan dan olahraga"],
            ["category_id" => 9, "question_id" => 55, "content" => "Saya menempatkan segala sesuatunya setrik rapih dan teratur"],

            ["category_id" => 6, "question_id" => 56, "content" => "Saya bergaul baik dengan semua orang"],
            ["category_id" => 10, "question_id" => 56, "content" => "Saya pandai mengendalikan diri"],

            ["category_id" => 17, "question_id" => 57, "content" => "Saya ingin berkenalan dengan orang-orang gres dan mengerjakan hal baru"],
            ["category_id" => 11, "question_id" => 57, "content" => "Saya selalu ingin menuntaskan pekerjaan yang sudah saya mulai"],

            ["category_id" => 18, "question_id" => 58, "content" => "Biasanya saya bersikeras mengenai apa yang saya yakini"],
            ["category_id" => 12, "question_id" => 58, "content" => "Biasanya saya suka bekerja keras"],

            ["category_id" => 19, "question_id" => 59, "content" => "Saya menyukai saran-saran dari orang-orang yang saya kagumi"],
            ["category_id" => 13, "question_id" => 59, "content" => "Saya bahagia mengatur orang lain"],

            ["category_id" => 20, "question_id" => 60, "content" => "Saya biarkan orang-orang lain mepengaruhi saya"],
            ["category_id" => 14, "question_id" => 60, "content" => "Saya suka mendapatkan banyak perhatian"],

            ["category_id" => 1, "question_id" => 61, "content" => "Biasanya saya bekerja sangat keras"],
            ["category_id" => 4, "question_id" => 61, "content" => "Biasanya saya bekerja cepat"],

            ["category_id" => 2, "question_id" => 62, "content" => "Bila saya berbitrik, kelompok akan mendengarkan"],
            ["category_id" => 5, "question_id" => 62, "content" => "Saya terampil memperfungsikan alat-alat kerja"],

            ["category_id" => 3, "question_id" => 63, "content" => "Saya lambat membina persahabatan"],
            ["category_id" => 6, "question_id" => 63, "content" => "Saya lambat dalam mengambil keputusan"],

            ["category_id" => 4, "question_id" => 64, "content" => "Biasanya saya makan setrik cepat"],
            ["category_id" => 7, "question_id" => 64, "content" => "Saya suka membaca"],

            ["category_id" => 5, "question_id" => 65, "content" => "Saya menyukai pekerjaan yang memungkinkan saya berkeliling"],
            ["category_id" => 8, "question_id" => 65, "content" => "Saya menyukai pekerjaan yang harus dilakukan setrik teliti"],

            ["category_id" => 6, "question_id" => 66, "content" => "Saya berteman sebanyak mungkin"],
            ["category_id" => 9, "question_id" => 66, "content" => "Saya sanggup menemukan hal-hal yang telah saya pindahkan"],

            ["category_id" => 7, "question_id" => 67, "content" => "Perencanaan saya jauh ke masa depan"],
            ["category_id" => 10, "question_id" => 67, "content" => "Saya selalu menyenangkan"],

            ["category_id" => 18, "question_id" => 68, "content" => "Saya merasa besar hati akan nama baik saya"],
            ["category_id" => 11, "question_id" => 68, "content" => "Saya tetap menekuni satu permasalahan hingga ia terselesaikan"],

            ["category_id" => 19, "question_id" => 69, "content" => "Saya suka menyenangkan hati orang-orang yang saya kagumi"],
            ["category_id" => 12, "question_id" => 69, "content" => "Saya suka menjadi seorang yang berhasil"],

            ["category_id" => 20, "question_id" => 70, "content" => "Saya bahagia bila orang-orang lain mengambil keputusan untuk kelompok"],
            ["category_id" => 13, "question_id" => 70, "content" => "Saya suka mengambil keputusan untuk kelompok"],

            ["category_id" => 1, "question_id" => 71, "content" => "Saya selalu berusaha sangat keras"],
            ["category_id" => 3, "question_id" => 71, "content" => "Saya cepat dan ringannya mengambil keputusan"],

            ["category_id" => 2, "question_id" => 72, "content" => "Biasanya kelompok saya mengerjakan hal-hal yang saya inginkan"],
            ["category_id" => 4, "question_id" => 72, "content" => "Biasanya saya tergesa-gesa"],

            ["category_id" => 3, "question_id" => 73, "content" => "Saya seringkali merasa lelah"],
            ["category_id" => 5, "question_id" => 73, "content" => "Saya lambat dalam mengambil keputusan"],

            ["category_id" => 4, "question_id" => 74, "content" => "Saya bekerja setrik cepat"],
            ["category_id" => 6, "question_id" => 74, "content" => "Saya ringannya menerima kawan"],

            ["category_id" => 5, "question_id" => 75, "content" => "Biasanya saya bersemangat atau bergairah"],
            ["category_id" => 7, "question_id" => 75, "content" => "Sebagian besar waktu saya untuk berpikir"],

            ["category_id" => 6, "question_id" => 76, "content" => "Saya sangat hangat kepada orang-orang"],
            ["category_id" => 8, "question_id" => 76, "content" => "Saya menyukai pekerjaan yang menuntut ketepatan"],

            ["category_id" => 7, "question_id" => 77, "content" => "Saya banyak berpikir dan merencana"],
            ["category_id" => 9, "question_id" => 77, "content" => "Saya meletakkan segala sesuatu pada tempatnya"],

            ["category_id" => 8, "question_id" => 78, "content" => "Saya suka kiprah yang perlu ditekuni hingga kepada hal sedetilnya"],
            ["category_id" => 10, "question_id" => 78, "content" => "Saya tidak cepat marah"],

            ["category_id" => 19, "question_id" => 79, "content" => "Saya bahagia mengikuti orang-orang yang saya kagumi"],
            ["category_id" => 11, "question_id" => 79, "content" => "Saya selalu menuntaskan pekerjaan yang saya mulai"],

            ["category_id" => 20, "question_id" => 80, "content" => "Saya menyukai petunjuk-petunjuk yang jelas"],
            ["category_id" => 12, "question_id" => 80, "content" => "Saya suka bekerja keras"],

            ["category_id" => 1, "question_id" => 81, "content" => "Saya mengejar apa yang saya inginkan"],
            ["category_id" => 2, "question_id" => 81, "content" => "Saya yakni seorang pemimpin yang baik"],

            ["category_id" => 2, "question_id" => 82, "content" => "Saya menciptakan orang lain bekerja keras"],
            ["category_id" => 3, "question_id" => 82, "content" => "Saya yakni seorang yang gampangan (tak banyak pertimbangan)"],

            ["category_id" => 3, "question_id" => 83, "content" => "Saya menciptakan keputusan-keputusan setrik cepat"],
            ["category_id" => 4, "question_id" => 83, "content" => "Bitrik saya cepat"],

            ["category_id" => 4, "question_id" => 84, "content" => "Biasanya saya bekerja tergesa-gesa"],
            ["category_id" => 5, "question_id" => 84, "content" => "Setrik teratur saya berolahraga"],

            ["category_id" => 5, "question_id" => 85, "content" => "Saya tidak suka bertemu dengan orang-orang"],
            ["category_id" => 6, "question_id" => 85, "content" => "Saya cepat lelah"],

            ["category_id" => 6, "question_id" => 86, "content" => "Saya memiliki aneka macam teman"],
            ["category_id" => 7, "question_id" => 86, "content" => "Banyak waktu saya untuk berfikir"],

            ["category_id" => 7, "question_id" => 87, "content" => "Saya suka bekerja dengan teori"],
            ["category_id" => 8, "question_id" => 87, "content" => "Saya suka bekerja sedetil-detilnya"],

            ["category_id" => 8, "question_id" => 88, "content" => "Saya suka bekerja hingga sedetil-detilnaya"],
            ["category_id" => 9, "question_id" => 88, "content" => "Saya suka mengorganisir pekerjaan saya"],

            ["category_id" => 9, "question_id" => 89, "content" => "Saya meletakkan segala sesuatu pada tempatnya"],
            ["category_id" => 10, "question_id" => 89, "content" => "Saya selalu menyenangkan"],
            
            ["category_id" => 20, "question_id" => 90, "content" => "Saya bahagia diberi petunjuk mengenai apa yang harus saya lakukan"],
            ["category_id" => 11, "question_id" => 90, "content" => "Saya harus menuntaskan apa yang sudah saya mulai"],
        ];

        foreach ($options as $option) {
            DB::table('psychotest_papikostick_options')->insert($option);
        }
    }
}
