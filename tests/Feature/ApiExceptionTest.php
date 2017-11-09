<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiExceptionTest extends TestCase
{
    /** @test */
    public function model_not_found_exception_test()
    {
        $response = $this->json('GET', 'api/v1/master/warehouses/9999', [], [$this->headers]);

        $response->assertJsonStructure([
            'error' => ['code', 'message'],
        ]);
    }

    /** @test */
    public function http_not_found_exception_test()
    {
        $response = $this->json('GET', 'unavailable/route', [], [$this->headers]);

        $response->assertJsonStructure([
            'error' => ['code', 'message'],
        ]);
    }
}
