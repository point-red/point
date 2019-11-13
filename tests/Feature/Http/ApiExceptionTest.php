<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

class ApiExceptionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function model_not_found_exception_test()
    {
        $this->signIn();

        config()->set('database.default', 'tenant');
        $response = $this->json('GET', '/api/v1/master/warehouses/9999', [], [$this->headers]);

        $response->assertJsonStructure(['code', 'message']);

        $response->assertJson([
            'code' => 404,
            'message' => 'Model not found.',
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
