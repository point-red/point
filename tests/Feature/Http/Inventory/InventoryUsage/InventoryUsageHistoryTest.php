<?php

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Tests\TestCase;

use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageHistoryTest extends TestCase
{
    use InventoryUsageSetup;

    public static $path = '/api/v1/inventory/usages';

    /** @test */
    public function success_create_inventory_usage()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }
    /** @test */
    // public function success_update_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $InventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
    //     $data = $this->getDummyData($InventoryUsage);
    //     $data = data_set($data, 'id', $InventoryUsage->id, false);

    //     $response = $this->json('PATCH', self::$path . '/' . $InventoryUsage->id, $data, $this->headers);

    //     $response->assertStatus(201);
    //     $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
    //     $this->assertDatabaseHas('user_activities', [
    //         'number' => $response->json('data.form.number'),
    //         'table_id' => $response->json('data.id'),
    //         'table_type' => 'SalesInventoryUsage',
    //         'activity' => 'Update - 1'
    //     ], 'tenant');
    // }
    /** @test */
    public function read_inventory_usage_histories()
    {
        $this->success_create_inventory_usage();
        // $this->success_update_inventory_usage();

        $InventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        // $InventoryUsageUpdated = InventoryUsage::orderBy('id', 'desc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $InventoryUsage->id . '/histories', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $InventoryUsage->form->number,
            // 'number' => $InventoryUsage->form->edited_number,
            'table_id' => $InventoryUsage->id,
            'table_type' => $InventoryUsage::$morphName,
            'activity' => 'Created'
        ], 'tenant');
        // $this->assertDatabaseHas('user_activities', [
        //     'number' => $InventoryUsageUpdated->form->number,
        //     'table_id' => $InventoryUsageUpdated->id,
        //     'table_type' => $InventoryUsageUpdated::$morphName,
        //     'activity' => 'Update - 1'
        // ], 'tenant');
    }
    /** @test */
    // public function success_create_inventory_usage_history()
    // {
    //     $this->success_create_inventory_usage();

    //     $InventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
    //     $data = [
    //         "id" => $InventoryUsage->id,
    //         "activity" => "Printed"
    //     ];

    //     $response = $this->json('POST', self::$path . '/' . $InventoryUsage->id . '/histories', $data, $this->headers);

    //     $response->assertStatus(201);
    //     $this->assertDatabaseHas('user_activities', [
    //         'number' => $response->json('data.number'),
    //         'table_id' => $response->json('data.table_id'),
    //         'table_type' => $response->json('data.table_type'),
    //         'activity' => $response->json('data.activity')
    //     ], 'tenant');
    // }
}
