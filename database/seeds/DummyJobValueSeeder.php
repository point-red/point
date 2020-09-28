<?php

use Illuminate\Database\Seeder;
use App\Model\Plugin\SalaryNonSales\Group;
use App\Model\Plugin\SalaryNonSales\FactorCriteria;
use App\Model\Plugin\SalaryNonSales\GroupFactor;

class DummyJobValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Dummy for job value on tenant database
     *
     * @return void
     */
    public function run()
    {
        $groups = [
            'Competence' => [
                'Pendidikan Formal',
                'Pengalaman',
            ],
            'Job Process' => [
                'Presentasi Usaha Fisik',
                'Tingkat Usaha Fisik'
            ]
        ];

        foreach($groups as $group => $factors) {
            $group = Group::firstOrCreate(['name' => $group]);

            foreach($factors as $factor) {
                $factor = GroupFactor::create([
                    'name' => $factor,
                    'group_id' => $group->id
                ]);
            }
        }

        $criterias = [
            'Pendidikan Formal' => [
                'SD Sederajat',
                'SMP Sederajat',
                'SMU Sederajat',
                'S1 & S2 Sederajat'
            ],
            'Pengalaman' => [
                '1 tahun kebawah',
                '1-5 thn',
                '5-10 thn',
                '10-15 thn',
                '> 15 thn'
            ],
            'Presentasi Usaha Fisik' => [
                '100% Administratif',
                '75% Administratif & 25% Fisik',
                '50% Administratif & 50% Fisik',
                '25% Administratif & 75% Fisik',
                '100% Fisik'
            ],
            'Tingkat Usaha Fisik' => [
                'tidak melakukan aktifitas fisik yang siknifikan, lebih banyak duduk dan berjalan hanya didalam ruangan',
                'melakukan aktifitas fisik yang setara dengan mengangkat/mendorong/menarik beban ringan <5kg',
                'melakukan aktifitas fisik yang setara dengan mengangkat/mendorong/menarik beban sedang 5kg - 20kg',
                'melakukan aktifitas fisik yang setara dengan mengangkat/mendorong/menarik beban berat >20kg - 30kg',
                'melakukan aktifitas fisik yang setara dengan mengangkat/mendorong/menarik beban sangat berat >30kg',
            ]
        ];

        foreach($criterias as $factor => $criteria) {
            $factor = GroupFactor::where('name', $factor)->first();
            foreach($criteria as $index => $desc) {
                FactorCriteria::firstOrCreate([
                    'level' => $index + 1,
                    'description' => $desc,
                    'score' => 1,
                    'factor_id' => $factor->id
                ]);
            }
        }


    }
}
