<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
