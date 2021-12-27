<?php

namespace Tests\Feature\Http\Plugins\PinPoint;

use Tests\TestCase;

class SimilarProductTest extends TestCase
{
    static $ENDPOINT = '/api/v1/plugin/pin-point/similar-products';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_similar_product()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('POST', self::$ENDPOINT, $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('pin_point_similar_products', $data, 'tenant');
    }

    /** @test */
    public function duplicate_similar_product()
    {
        $this->json('POST', self::$ENDPOINT, ['name' => 'similar'], [$this->headers]);
        $response2 = $this->json('POST', self::$ENDPOINT, ['name' => 'similar'], [$this->headers]);

        $response2->assertStatus(400);
    }

}
