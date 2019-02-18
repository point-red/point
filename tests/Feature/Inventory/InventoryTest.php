<?php

namespace Tests\Feature\Inventory;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function get_inventory_test()
    {
//        $items = factory(Item::class, 10)->create();
//        $warehouses = factory(Item::class, 2)->create();
//        $supplier = factory(Supplier::class)->create();

        $response = $this->json('GET', 'api/v1/inventory', [
            'ignore_empty' => true
        ], [$this->headers]);

        log_object(json_decode($response->getContent()));

//        foreach ($warehouses as $warehouse) {
//            $this->assertDatabaseHas('warehouses', [
//                'code' => $warehouse->code,
//                'name' => $warehouse->name,
//            ], 'tenant');
//        }

        $response->assertStatus(200);
    }

    /** @test */
    public function find_inventory_test()
    {
//        $items = factory(Item::class, 10)->create();
//        $warehouses = factory(Item::class, 2)->create();
//        $supplier = factory(Supplier::class)->create();

        $response = $this->json('GET', 'api/v1/inventory/1', [], [$this->headers]);

        log_object(json_decode($response->getContent()));

//        foreach ($warehouses as $warehouse) {
//            $this->assertDatabaseHas('warehouses', [
//                'code' => $warehouse->code,
//                'name' => $warehouse->name,
//            ], 'tenant');
//        }

        $response->assertStatus(200);
    }
}
