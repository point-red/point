<?php

namespace Tests;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends PointTestCase
{
    use CreatesApplication;

    /**
     *  Set up the test.
     */
    public function setUp()
    {
        parent::setUp();

        Artisan::call('config:clear');

        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        DB::connection('mysql')->reconnect();
        DB::connection('tenant')->reconnect();
    }

    protected function signIn()
    {
        $user = User::first();

        $this->actingAs($user, 'api');
    }
}
