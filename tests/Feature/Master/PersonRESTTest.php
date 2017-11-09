<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Throwable;

class PersonRESTTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_person()
    {

    }

    /** @test */
    public function an_user_can_read_single_person()
    {

    }

    /** @test */
    public function an_user_can_read_all_person()
    {

    }

    /** @test */
    public function an_user_can_update_person()
    {

    }

    /** @test */
    public function an_user_can_delete_person()
    {

    }
}
