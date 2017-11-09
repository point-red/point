<?php

use App\User;
use App\Model\Master\Person;
use App\Model\Master\Warehouse;
use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DefaultSeeder::class);

        factory(User::class, 10)->create();
        factory(Person::class, 10)->create();
        factory(Warehouse::class, 10)->create();
    }
}
