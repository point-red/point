<?php

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Tests\TestCase;

use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageApprovalTest extends TestCase
{
    use InventoryUsageSetup;

    public static $path = '/api/v1/inventory/usages';

    private $previousInventoryUsageData;

    /** @test */
    public function success_create_inventory_usage($isFirstCreate = true)
    {
        $data = $this->getDummyData();
        
        if($isFirstCreate) {
            $this->setRole();
            $this->previousInventoryUsageData = $data;
        }

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
    public function unauthorized_approve_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function success_approve_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_by' => $response->json('data.form.approval_by'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Approved'
        ], 'tenant');
    }

    /** @test */
    public function not_found_form_approve_inventory_usage()
    {
        $this->success_approve_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Approval not found"
            ]);
    }

    /** @test */
    public function unauthorized_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function invalid_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Rejected'
        ], 'tenant');
    }

    /** @test */
    public function not_found_form_reject_inventory_usage()
    {
        $this->success_reject_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Approval not found"
            ]);
    }
    
    /** @test */
    // public function success_read_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $data = [
    //         'join' => 'form,customer,items,item',
    //         'fields' => 'sales_inventory_usage.*',
    //         'sort_by' => '-form.number',
    //         'group_by' => 'form.id',
    //         'filter_form'=>'notArchived;null',
    //         'filter_like'=>'{}',
    //         'filter_date_min'=>'{"form.date":"2022-05-01 00:00:00"}',
    //         'filter_date_max'=>'{"form.date":"2022-05-17 23:59:59"}',
    //         'includes'=>'form;customer;warehouse;items.item;items.allocation',
    //         'limit'=>10,
    //         'page' => 1
    //     ];

    //     $response = $this->json('GET', self::$path . '/approval', $data, $this->headers);
        
    //     $response->assertStatus(200);
    // }

    /** @test */
    // public function success_send_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
    //     $data['ids'][] = ['id' => $inventoryUsage->id];

    //     $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    //     $response->assertStatus(200);
    // }

    /** @test */
    // public function success_send_multiple_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $this->success_create_inventory_usage($isFirstCreate = false);
    //     $inventoryUsage = InventoryUsage::orderBy('id', 'desc')->first();
    //     $inventoryUsage->form->request_approval_to = $this->previousInventoryUsageData['request_approval_to'];
    //     $inventoryUsage->form->save();

    //     $data['ids'] = InventoryUsage::get()
    //         ->pluck('id')
    //         ->map(function ($id) { return ['id' => $id]; })
    //         ->toArray();

    //     $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    //     $response->assertStatus(200);
    // }
}
