<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function test_sample()
    {
        $response = $this->get('/', $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function test_sample_a()
    {
        $response = $this->get('/', $this->headers);
        $response->assertStatus(200);
    }
}
