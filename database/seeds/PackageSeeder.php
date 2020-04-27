<?php

use App\Model\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $package = new Package;
        $package->code = 'A000';
        $package->name = 'COMMUNITY EDITION';
        $package->description = '';
        $package->max_user = 1;
        $package->price = 0;
        $package->price_per_user = 0;
        $package->is_active = true;
        $package->save();

        $package = new Package;
        $package->code = 'A001';
        $package->name = 'BASIC';
        $package->description = '1. Suitable for smaller businesses\n2. Basic resource management features\n3. Up to 10 users';
        $package->max_user = 10;
        $package->price = 1000000;
        $package->price_per_user = 30000;
        $package->is_active = true;
        $package->save();

        $package = new Package;
        $package->code = 'A002';
        $package->name = 'BASIC';
        $package->description = '1. Suitable for medium businesses\n2. Basic resource management features\n3. Up to 100 users';
        $package->max_user = 100;
        $package->price = 3000000;
        $package->price_per_user = 30000;
        $package->is_active = true;
        $package->save();

        $package = new Package;
        $package->code = 'A003';
        $package->name = 'BASIC';
        $package->description = '1. Suitable for larger businesses\n2. advance resource management features\n3. Unlimited users';
        $package->max_user = 999;
        $package->price = 9000000;
        $package->price_per_user = 30000;
        $package->is_active = true;
        $package->save();
    }
}
