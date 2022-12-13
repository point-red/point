<?php

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

use App\Mail\Inventory\InventoryUsageApprovalMail;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use App\Model\Master\Item;

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

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
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
    public function iu_ef1_unauthorized_branch_update_inventory_usage()
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
    public function iu_ef2_unauthorized_warehouse_update_inventory_usage()
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
    public function iu_ef5_unauthorized_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $this->unsetUserRole();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($inventoryUsage);

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }
    /** @test */
    public function iu_ef6_invalid_date_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'date', '1970-01-01', true); // date less than create

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
                "errors" => [
                    "date" => ["The date must be a date after or equal to {$inventoryUsage->form->date}."],
                ]
            ]);
    }
    /** @test */
    public function iu_ef7_invalid_update_inventory_usage()
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
    public function iu_ef9_overquantity_update_inventory_usage()
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
    public function iu_ef10_invalid_unit_update_inventory_usage()
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
    public function iu_ef11_invalid_productionnumber_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);

        $dataUsageItem = $this->getDummyDataItem($isItemDna = true);
        $dataUsageItem = data_set($dataUsageItem, 'dna.0.production_number', null, true);

        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'items.1', $dataUsageItem, false); // item dna without production number

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);   
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => 'Production Number for Item '.$dataUsageItem['item_name'].' not found'
            ]);
    }
    /** @test */
    public function iu_ef12_invalid_expirydate_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);

        $dataUsageItem = $this->getDummyDataItem($isItemDna = true);
        $dataUsageItem = data_set($dataUsageItem, 'dna.0.expiry_date', null, true);

        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'items.1', $dataUsageItem, false); // item dna without expry date

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);   
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Expiry Date for Item -{$dataUsageItem['item_name']} not found"
            ]);
    }
    /** @test */
    public function iu_ef16_invalid_notes_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'notes', str_pad('', 500, 'X', STR_PAD_LEFT), true); // over maximum length of notes
        
        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
                "errors" => [
                    "notes" => ["The notes may not be greater than 255 characters."],
                ]
            ]);
    }
    /** @test */
    public function iu_ef17_invalid_warehouse_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'warehouse_id', 99, true); // random warehouse_id

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Warehouse Test warehouse not set as default"
            ]);
    }
    /** @test */
    public function iu_ef18_invalid_item_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'items.0.item_id', 99, true); // random item_id

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
                "errors" => [
                    "items.0.item_id" => ["The selected items.0.item_id is invalid."],
                ]
            ]);
    }
    /** @test */
    public function iu_ef19_invalid_account_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);
        $data = data_set($data, 'items.0.chart_of_account_id', 99, true); // random item_id

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
                "errors" => [
                    "items.0.chart_of_account_id" => ["The selected items.0.chart_of_account_id is invalid."],
                ]
            ]);
    }
    /** @test */
    public function success_update_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($inventoryUsage);
        $data = data_set($data, 'id', $inventoryUsage->id, false);

        Mail::fake();

        $response = $this->json('PATCH', self::$path . '/' . $inventoryUsage->id, $data, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('forms', [ 
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Update - 1'
        ], 'tenant');

        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.form.id'),
            'journalable_type' => Item::$morphName,
            'journalable_id' => $response->json('data.items.0.item_id'),
            'chart_of_account_id' => $response->json('data.items.0.chart_of_account_id'),
        ], 'tenant');

        Mail::assertQueued(InventoryUsageApprovalMail::class);
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
