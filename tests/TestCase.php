<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     *  Set up the test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');

        $this->artisan('passport:install');

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function signIn($user = null)
    {
        $this->user = $user ?: factory(User::class)->create();

        Passport::actingAs($this->user, ['*']);

        return $this;
    }
}
