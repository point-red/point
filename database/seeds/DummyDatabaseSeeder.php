<?php

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

        factory(\App\User::class, 10)->create();
        factory(\App\Model\Project\Project::class, 3)->create();
    }
}
