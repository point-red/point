<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiExceptionTest extends TestCase
{
    /** @test */
    public function model_not_found_exception_test()
    {
        config()->set('database.default', 'point_tenant_test');
        $response = $this->json('GET', '/api/v1/master/warehouses/9999', [], [$this->headers]);

        $response->assertJsonStructure(['code', 'message']);

        $response->assertJson([
            'code' => 401,
            'message' => 'Unauthenticated.',
        ]);
    }

    /** @test */
    public function http_not_found_exception_test()
    {
        $response = $this->json('GET', '/unavailable/route', [], [$this->headers]);

        $response->assertJsonStructure(['code', 'message']);

        $response->assertJson([
            'code' => 404,
            'message' => 'Http not found.'
        ]);
    }
}
