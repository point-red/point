<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     *  Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:fresh');

        $this->artisan('passport:install');
    }

}
