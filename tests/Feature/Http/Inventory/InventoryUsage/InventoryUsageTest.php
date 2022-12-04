<?php

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Tests\TestCase;

use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageTest extends TestCase
{
    use InventoryUsageSetup;

    public static $path = '/api/v1/inventory/usages';

    /** @test */
    public function unauthorized_branch_create_inventory_usage()
    {
        $this->setRole('inventory');
        $this->setPermission('create inventory usage');

        $this->branchDefault = null;
        foreach ($this->tenantUser->branches as $branch) {
            $branch->pivot->is_default = false;
            $branch->pivot->save();
        }
        
        $data = $this->getDummyData();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "please set default branch to create this form"
            ]);
    }
    /** @test */
    public function unauthorized_warehouse_create_inventory_usage()
    {
        $this->setRole('inventory');
        $this->setPermission('create inventory usage');

        // make warehouse request difference with use default warehouse 
        $this->warehouseSelected = $this->createWarehouse($this->branchDefault);

        $data = $this->getDummyData();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Warehouse Test warehouse not set as default"
            ]);
    }
    /** @test */
    public function unauthorized_create_inventory_usage()
    {
        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `create inventory usage` for guard `api`."
            ]);
    }
    /** @test */
    public function overquantity_create_inventory_usage()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data = data_set($data, 'items.0.quantity', 1000);

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Stock {$data['items'][0]['item_name']} not enough"
            ]);
    }
    /** @test */
    public function invalid_create_inventory_usage()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data = data_set($data, 'items.0.chart_of_account_id', null);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
            ]);
    }
    /** @test */
    public function invalid_unit_create_inventory_usage()
    {
        $this->setRole();

        $data = $this->getDummyData(null, $itemUnit = 'box');

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "there are some item not in 'pcs' unit"
            ]);
    }
    /** @test */
    public function invalid_dna_create_inventory_usage()
    {
        $this->setRole();

        $data = $this->getDummyData(null, $itemUnit = 'pcs', $isItemDna = true);

        $response = $this->json('POST', self::$path, $data, $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "there are some item not in 'pcs' unit"
            ]);
    }
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
                'approval_status' => 1,
            ], 'tenant');
    }
    /** @test */
    public function read_all_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $data = [
            'join' => 'form,warehouse,items,item',
            'fields' => 'inventory_usage.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"' . date('Y-m-01') . ' 00:00:00"}',
            'filter_date_max' => '{"form.date":"' . date('Y-m-30') . ' 23:59:59"}',
            'limit' => 10,
            'includes' => 'form;warehouse;items;items.item',
            'page' => 1
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "form" => [
                            "approval_status",
                            "date",
                            "done",
                            "notes",
                            "number",
                        ],
                        "items" => [
                            [
                                "expiry_date",
                                "id",
                                "item" => [
                                    "id",
                                    "name"
                                ],
                                "item_id",
                                "notes",
                                "production_number",
                                "quantity",
                                "unit",
                            ]
                        ]
                    ],
                ]
            ]);
    }
    /** @test */
    public function read_inventory_usage()
    {
        $this->success_approve_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = [
            'with_archives' => 'true',
            'with_origin' => 'true',
            'includes' => 'warehouse;items.account;items.item;items.allocation;form.createdBy;form.requestApprovalTo;form.requestCancellationTo;employee'
        ];

        $response = $this->json('GET', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    "employee" => [
                        "id",
                        "name",
                    ],
                    "employee_id",
                    "form" => [
                        "approval_at",
                        "approval_status",
                        "created_by" => [
                            "first_name",
                            "full_name",
                            "id",
                            "last_name",
                        ],
                        "date",
                        "done",
                        "id",
                        "notes",
                        "number",
                        "request_approval_at",
                        "request_approval_to" => [
                            "email",
                            "first_name",
                            "full_name",
                            "id",
                            "last_name",
                        ],
                    ],
                    "id",
                    "items" => [
                        [
                            "account" => [
                                "id",
                                "alias",
                                "label",
                                "number"
                            ],
                            "allocation" => [
                                "id",
                                "name",
                            ],
                            "allocation_id",
                            "expiry_date",
                            "id",
                            "item" => [
                                "code",
                                "id",
                                "label",
                                "name"
                            ],
                            "item_id",
                            "notes",
                            "production_number",
                            "quantity",
                            "unit",
                        ]
                    ],
                    "warehouse" => [
                        "id",
                        "name",
                    ],
                    "warehouse_id"
                ],
            ]);
    }
    /** @test */
    public function unauthorized_branch_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $this->setRole('inventory');
        $this->setPermission('update inventory usage');

        $this->branchDefault = null;
        foreach ($this->tenantUser->branches as $branch) {
            $branch->pivot->is_default = false;
            $branch->pivot->save();
        }
        
        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($inventoryUsage);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "please set default branch to update this form"
            ]);
    }
    /** @test */
    public function unauthorized_warehouse_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $this->setRole('inventory');
        $this->setPermission('update inventory usage');

        // make warehouse request difference with use default warehouse 
        $this->warehouseSelected = $this->createWarehouse($this->branchDefault);
        
        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($inventoryUsage);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Warehouse Test warehouse not set as default"
            ]);
    }
    /** @test */
    public function unauthorized_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $this->unsetUserRole();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($inventoryUsage);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `update inventory usage` for guard `api`."
            ]);
    }
    /** @test */
    public function overquantity_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'items.0.quantity', 2000);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Stock {$data['items'][0]['item_name']} not enough"
            ]);
    }
    /** @test */
    public function invalid_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'request_approval_to', null);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
            ]);
    }
    /** @test */
    public function invalid_unit_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage, $itemUnit = 'box');
        $data = data_set($data, 'id', $inventoryUsage->id, false);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "there are some item not in 'pcs' unit"
            ]);
    }
    /** @test */
    public function success_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Update - 1'
        ], 'tenant');
    }
    /** @test */
    // public function unauthorized_delete_delivery_order()
    // {
    //     $this->success_create_delivery_order();

    //     $this->unsetUserRole();

    //     $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
    //     $data['reason'] = $this->faker->text(200);

    //     $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

    //     $response->assertStatus(500)
    //         ->assertJson([
    //             "code" => 0,
    //             "message" => "There is no permission named `delete sales delivery order` for guard `api`."
    //         ]);
    // }
    /** @test */
    // public function success_delete_delivery_order()
    // {
    //     $this->success_create_delivery_order();

    //     $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
    //     $data['reason'] = $this->faker->text(200);

    //     $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

    //     $response->assertStatus(204);
    //     $this->assertDatabaseHas('forms', [
    //         'number' => $deliveryOrder->form->number,
    //         'request_cancellation_reason' => $data['reason'],
    //         'cancellation_status' => 0,
    //     ], 'tenant');
    // }
    /** @test */
    // public function failed_export_delivery_order()
    // {
    //     $this->setRole();

    //     $headers = $this->headers;
    //     unset($headers['Tenant']);

    //     $data = [
    //         'join' => 'form,customer,items,item',
    //         'fields' => 'sales_delivery_order.*',
    //         'sort_by' => '-form.number',
    //         'group_by' => 'form.id',
    //         'filter_form' => 'notArchived;null',
    //         'filter_like' => '{}',
    //         'filter_date_min' => '{"form.date":"2022-05-01 00:00:00"}',
    //         'filter_date_max' => '{"form.date":"2022-05-08 23:59:59"}',
    //         'limit' => 10,
    //         'includes' => 'form;customer;warehouse;items.item;items.allocation',
    //         'page' => 1
    //     ];

    //     $response = $this->json('GET', self::$path . '/export', $data, $headers);
    //     $response->assertStatus(500);
    // }
    /** @test */
    // public function success_export_delivery_order()
    // {
    //     $this->setRole();

    //     $data = [
    //         'join' => 'form,customer,items,item',
    //         'fields' => 'sales_delivery_order.*',
    //         'sort_by' => '-form.number',
    //         'group_by' => 'form.id',
    //         'filter_form' => 'notArchived;null',
    //         'filter_like' => '{}',
    //         'filter_date_min' => '{"form.date":"2022-05-01 00:00:00"}',
    //         'filter_date_max' => '{"form.date":"2022-05-08 23:59:59"}',
    //         'limit' => 10,
    //         'includes' => 'form;customer;warehouse;items.item;items.allocation',
    //         'page' => 1
    //     ];

    //     $response = $this->json('GET', self::$path . '/export', $data, $this->headers);

    //     $response->assertStatus(200)->assertJsonStructure([ 'data' => ['url'] ]);
    // }
}
