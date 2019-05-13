<?php

namespace App\Console\Commands;

use App\Model\Master\Group;
use App\Model\Master\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PriorityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:priority';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Priority';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Surabaya');
        $prs = 'Blue Star Karaoke;Suka Suka Karaoke;Warehouse Pantai Mentari;Warkop D\'mojo Merr;Warkop Mahabharata;warkop nusantara;Depot Pak Bas Pojok;Warkop 73 Sutorejo;Tk.perdana;Warkop Salman Cafe;Depot Hj. Laugi;G Suites Hotel;Mini Market Kevin;Vido Garment Kertajaya;Warkop biru;Warkop\'e Kang Ucup;Hotel Neo Jl. Jawa;Warkop Bu Win;Warkop Hilal;Kedai Spirit;DR Apartment Sukolilo;Bromo Swalayan Sukolilo;Depot Karepmu;Grand Royal Ballroom;Kitto Swalayan Mulyosari;The Alimar Premiere Hotel Surabaya;Warkop Etan Gebang;Warkop KTT7;Warkop Adoeh Bojo;Warkop Al Vian;CB Warkop TT;Toko Bahrul Ulum Sopoyono;Toko Dwi Putri Pandugo;Warkop Mak Eya;Warkop Smile;Warkop Uyee *;Warkop Mandiri Motor;Padmaning Cafe;Warkop AMPM;Warkop Rompi;wk. Tuyo 27;wk. Maestro;wk. RAM;wk. Giras;wk. Langgeng 99;wk. Barokah;wk. Cak open;Tk. Puji jaya;Tk. Barokah;Tk. Air mancur;Hotel AMARIS;wk.waras;kedai kopi deswita;jl.kalijudan merr 196;wk.67;giras 81;wk.fauzy;wk.rock boyo;giras 71;tk.quba;tk.gaul;Warkop GG 9;Warkop Etan;warkop angling darmo;Warkop bagong gaul 22;Warkop 86;Warkop seduluran;Warkop sumber berkah;Warkop gaza;Warkop ijo;Warkop 57;Warung kuning(warning!!!);warkop hokage;Warkop bolodewe;Warkop 113C;warkop dulur dewe;Warung bu ni;Toko kurnia;Toko amanah;Warkop 29;Warkop Indo;Giras Sans;Warkop Ijo;Warkop Fences;Warkop 27;Warkop Biru;Warkop Sido mampir;Toko Ari Jaya;Toko Avita;Warkop WM2;Warkop Giras Gaul;Naura Coffee;Warkop 70;King Coffee;Warkop Omah Abang;Warkop Jagongan;Warkop Giras;toko arjuna;toko sinar terang';
        $this->prior('point_kbsurabaya', $prs);

        $this->line('Mojokerto');
        $prs = 'Toko barokah;TK sumber Rejeki;aliyah swalayan;TK djoyo;TK dua Syahputra;TK Laura;TK Rahmad Jaya;TK sri Rejeki;TK snack Prajurit;tk Jaya;warkop Jacost;wk d kopies;wk kopi o;kedai payung;wk dejaka;wk abel 29;toko B2;garuda cell_;toko anugrah;toko lestari;toko berkah;toko nanda;toko bu sutomo;toko sumber lancar;mojo mart;toko aristani;pondok kopi 2;pondok us kopi;warkop gubung;warkop Rt;warkop coc;warkop pemancingan;tk.sumber rejeki.;tk munir endang.;tk.sodiq;tk pertigaan.;tk sido jodo.;tk orange.;tk ulum;tk sabar;tk sulis;tk prayit.;warkop sholik.;warkop mbak tri;cafe original.;warkop pandawa.;warkop istana musik.';
        $this->prior('point_kbmojokerto', $prs);

        $this->line('Banyuwangi');
        $prs = 'Toko Damai 614;Toko Pak andre;Rafi Mart;Wandira;AL - Market;Abi Mart;Toko Sinar Santoso;Toko Mochtar;Toko Linda;Toko Palupi;Toko TriWangi;Toko Sumber Agung;Toko Tentrem;Dafa Mart;Toko Nurrindah;Toko Sabilah;Toko Nyoto Joyo;Toko Sari Agung;Toko Rahayu;MD Mart;Warung Kemiren;Warkop Pak Tohir;Warung Silir-silir;Warung Gajah Oling;Warung Mas Boy;Warung Rajawali;Momo Kitchen;Garden Resto;Amdani;Warung Mak isun;Cmith Coffe;Sing sang Café;Cinamon Café;Warung Walet 77;Warung Bu Poer;Warkop 69;Java Sunrise;Osing Deles;Vio mart;Vionata;Toko Gunawan;Arraihan Mart;MM Azahra;Toko Mentari 2;Toko 510;Toko Almira;Toko AL Hidayah;Toko warna;Toko Wijoyo;Toko Sembako Ridho;Toko Damar;Toko Vitali;Toko Khanza;Toko Sampurna 2;Toko Imran;Toko Indah jaya;Kopine Café;Kedai 52;Toko Pak Mad;Toko waratama;Roxy;Warkop Cangkrukan;Warkop Kaliseng;Sindrom Café;Warung OI;Warkop Mb Mila;Warkop Gor Twangalun;Warkop Sumber Wangi;Warles;Warung Pojok PLN;Warung Kaisar;Angkringan Piyu;Pondok Indah;Kedai Raffa;Warung Kismis;Warkop Budi Asto;Warkop Susi;Kedai GM coffe;Warung Anugrah;Café Kemunir;Toko santoso;Toko Bu Ulfa;Can Con Mart;Toko Sri  Rejeki;Toko Jago;Toko Nurinda;Toko Yulianto;Toko Majapahit;Toko Karuna;Toko Pojok;Toko Indrasari;Toko Sugik;Toko sembako azzahra;Toko AA;Toko Sisil;Toko Mulia;Toko Ryan;Toko Dizza;Toko Panderejo;Toko Mbok Puah;Warkop Campus;Warung IJO;Warkop Ugrik;Warkop MOBA;Warkop Om Rebut;Café Reddoorrs;Waroeng D\'Vega;Warung Kadek;Café Campus;Warkop Hore;Kedai 86;Warkop Katrok #1;Warkop Katrok #2;Café Jaran Goyang;Warkop Lohkanti;Warkop Katrok #3;Super Mart;Boboci Mart;Kedai Republik Osing;Toko WK;Toko Noer Ceh;Toko Pitagoras;Toko ely;Toko mbak Ida;Toko Baru;Toko Lord Jaya;Toko D3 Barokah;Toko Bu Anna;Toko Toyo Roso;Toko RERE;Toko Surya;Toko Bintang Mas;Toko Nadifa;Toko Balil;Toko Dua Cahaya;Toko Bu Sri;Toko Azka;Toko Diera;Toko Lumayan;Toko Barokah;Toko Kamelia;Toko Sawang Sae ;Toko Ashira;Toko Barokah;Warkop Pojok;Uumi Mart;Toko Raffi;Dapoer Nyitnyut;Waroeng Sri;Kantin Satu Tujuh;Toko Arista;Toko Delta;Toko Alamanda;Café Xena Balog;Warkop 43;Kedai Black Jack;Kedai Bang roni ;Angkringan Sobo;Warkop DPS;Red Papper Café;Toko Trian Jaya;Toko Billal;Toko Ghazi;Toko Pak Subhan;Toko Naning;Toko Mb Neni;Toko Virda;Toko majapahit;Toko Pandawa;Toko Mas Dius;Toko Rasya;Toko Si Komo;Toko Diera;Toko Barokah;Toko Bu Karsih;Toko Dizza;Toko Mb Lilik;Toko MB LINDA;toko Rizky 99;Toko Lia Gaplek;Warkop pak Sofyan;Kedai Bassmallah;Waarung Mama Mia;Warung Bu Lamina;Wrung Siliwangi;Warung Ndeso;Warung Fendi;Warung Aneka;Warung AL Barokah;Warung Adit;Kedai Awan bengi;Warung As Sunnah;Kedai Bamboo;Warkop Kantor Pos;Warung Bu Hos;Warung Mbok Hom;Warung Laros Ijen;Warung Arjuna;Warung UD Hanan;Warung badean raya;Toko Alinna;GGSP Smartshop;Toko Dayun;Toko Mawarid;Toko Mas Nur;Toko pak Sukri;Toko Anekadus;Toko Berkah;Gunung Ijen Mart;Rizky Karomah;Toko Syafa\'atul anwar;Toko Sumber Tani;Toko Mentari 2;Toko Setia Kawan;Toko Aulia;Toko Asslamualaikum;Toko Sherly;Toko Azzahra;Toko Mia;Toko Pitagoras;Warung TRISIA;Kedai family;Warung Sumber wangi;warung Samudra;Warkop Bang roni;Warung Bedho Dewe;Warkop Bandhik;Pondok Ketan;Warung D\'ummi;Warung Kya2;Warung Sumber rezeki;Kedai Litadaz;Warkop Sarema;Warung Jimmy;Warkop Cangkruk;Warung acha;Warkop Ikanza;warkop Panda;Warung Kang Yani;Warkop Bundo';
        $this->prior('point_kbbanyuwangi', $prs);
    }

    public function prior($table, $prs)
    {
        config()->set('database.connections.tenant.database', $table);
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        $group = Group::first();

        if (! $group) {
            $this->line($table.' has no group ');
            $group = new Group;
            $group->name = 'priority';
            $group->type = 'Customer';
            $group->save();
        } elseif ($group->name == 'priority') {
            $group->type = 'Customer';
            $group->save();
        }

        $customers = explode(';', $prs);
        $array = [];
        foreach ($customers as $customerName) {
            $customer = Customer::where('name', $customerName)->first();
            if ($customer) {
                if ($customer->groups) {
                    array_push($array, $customer->id);
                }
            }
        }
        if (count($array)) {
            $group->customers()->sync($array);
        }

        DB::connection('tenant')->commit();
    }
}
