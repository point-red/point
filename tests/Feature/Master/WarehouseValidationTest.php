<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\Master\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_warehouse_code_and_name_should_be_unique()
    {
        $warehouse = factory(Warehouse::class)->create();
        $data = [
            'code' => $warehouse->code,
            'name' => $warehouse->name,
        ];

        $response = $this->json('POST', 'api/v1/master/warehouses', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['code', 'name'],
            ],
        ]);

        $response->assertStatus(422);
    }
}
