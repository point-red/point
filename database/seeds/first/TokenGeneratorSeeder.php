<?php

use Illuminate\Database\Seeder;
use App\Model\Reward\TokenGenerator;

class TokenGeneratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tokenGenerator = new TokenGenerator;
        $tokenGenerator->source = 'sales visitation call';
        $tokenGenerator->amount = 1;
        $tokenGenerator->save();

        $tokenGenerator = new TokenGenerator;
        $tokenGenerator->source = 'sales visitation effective call';
        $tokenGenerator->amount = 2;
        $tokenGenerator->save();
    }
}
