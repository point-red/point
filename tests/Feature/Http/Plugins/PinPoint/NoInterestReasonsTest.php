<?php

namespace Tests\Feature\Http\Plugins\PinPoint;

use Tests\TestCase;

class NoInterestReasonsTest extends TestCase
{
    static $ENDPOINT = '/api/v1/plugin/pin-point/no-interest-reasons';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_no_interest_reasons()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('POST', self::$ENDPOINT, $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('pin_point_no_interest_reasons', $data, 'tenant');
    }

    /** @test */
    public function duplicate_no_interest_reasons()
    {
        $this->json('POST', self::$ENDPOINT, ['name' => 'duplicate'], [$this->headers]);
        $response2 = $this->json('POST', self::$ENDPOINT, ['name' => 'duplicate'], [$this->headers]);

        $response2->assertStatus(400);
    }

}
