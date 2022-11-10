<?php

namespace Tests\Feature\Http\Purchase;

use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use PurchaseOrderSetup;

    private $jsonShowPurchaseOrder = [
        "data" => [
            "id",
            "purchase_request_id",
            "purchase_contract_id",
            "supplier_id",
            "supplier_name",
            "supplier_address",
            "supplier_phone",
            "billing_address",
            "billing_phone",
            "billing_email",
            "shipping_address",
            "shipping_phone",
            "shipping_email",
            "warehouse_id",
            "eta",
            "cash_only",
            "need_down_payment",
            "delivery_fee",
            "discount_percent",
            "discount_value",
            "type_of_tax",
            "tax",
            "amount",
            "supplier" => [
                "id",
                "code",
                "tax_identification_number",
                "name",
                "address",
                "city",
                "state",
                "country",
                "zip_code",
                "latitude",
                "longitude",
                "phone",
                "phone_cc",
                "email",
                "notes",
                "branch_id",
                "created_by",
                "updated_by",
                "archived_by",
                "created_at",
                "updated_at",
                "archived_at",
                "label",
            ],
            "items" => [
                "*" => [
                    "id",
                    "purchase_order_id",
                    "purchase_request_item_id",
                    "item_id",
                    "item_name",
                    "quantity",
                    "price",
                    "discount_percent",
                    "discount_value",
                    "taxable",
                    "unit",
                    "converter",
                    "notes",
                    "allocation_id",
                    "item" => [
                        "id",
                        "chart_of_account_id",
                        "code",
                        "barcode",
                        "name",
                        "size",
                        "color",
                        "weight",
                        "notes",
                        "taxable",
                        "require_production_number",
                        "require_expiry_date",
                        "stock",
                        "stock_reminder",
                        "unit_default",
                        "unit_default_purchase",
                        "unit_default_sales",
                        "created_by",
                        "updated_by",
                        "archived_by",
                        "created_at",
                        "updated_at",
                        "archived_at",
                        "label",
                    ],
                    "allocation",
                ],
            ],
            "purchase_request" => [
                "id",
                "required_date",
                "supplier_id",
                "supplier_name",
                "supplier_address",
                "supplier_phone",
                "amount",
                "form" => [
                    "id",
                    "branch_id",
                    "date",
                    "number",
                    "edited_number",
                    "edited_notes",
                    "notes",
                    "created_by",
                    "updated_by",
                    "done",
                    "increment",
                    "increment_group",
                    "formable_id",
                    "formable_type",
                    "request_approval_to",
                    "approval_by",
                    "approval_at",
                    "approval_reason",
                    "approval_status",
                    "request_cancellation_to",
                    "request_cancellation_by",
                    "request_cancellation_at",
                    "request_cancellation_reason",
                    "cancellation_approval_at",
                    "cancellation_approval_by",
                    "cancellation_approval_reason",
                    "cancellation_status",
                    "created_at",
                    "updated_at",
                    "request_close_to",
                    "request_close_by",
                    "request_close_at",
                    "request_close_reason",
                    "close_approval_at",
                    "close_approval_by",
                    "close_status",
                    "request_approval_at",
                    "close_approval_reason",
                ],
            ],
            "form" => [
                "id",
                "branch_id",
                "date",
                "number",
                "edited_number",
                "edited_notes",
                "notes",
                "created_by" => [
                    "id",
                    "name",
                    "first_name",
                    "last_name",
                    "address",
                    "phone",
                    "email",
                    "created_at",
                    "updated_at",
                    "branch_id",
                    "warehouse_id",
                    "full_name",
                ],
                "updated_by",
                "done",
                "increment",
                "increment_group",
                "formable_id",
                "formable_type",
                "request_approval_to" => [
                    "id",
                    "name",
                    "first_name",
                    "last_name",
                    "address",
                    "phone",
                    "email",
                    "created_at",
                    "updated_at",
                    "branch_id",
                    "warehouse_id",
                    "full_name",
                ],
                "approval_by",
                "approval_at",
                "approval_reason",
                "approval_status",
                "request_cancellation_to",
                "request_cancellation_by",
                "request_cancellation_at",
                "request_cancellation_reason",
                "cancellation_approval_at",
                "cancellation_approval_by",
                "cancellation_approval_reason",
                "cancellation_status",
                "created_at",
                "updated_at",
                "request_close_to",
                "request_close_by",
                "request_close_at",
                "request_close_reason",
                "close_approval_at",
                "close_approval_by",
                "close_status",
                "request_approval_at",
                "close_approval_reason",
                "branch" => [
                    "id",
                    "name",
                    "address",
                    "phone",
                    "created_by",
                    "updated_by",
                    "archived_by",
                    "created_at",
                    "updated_at",
                    "archived_at",
                ]
            ]
        ]
    ];

    private $jsonIndexPurchaseOrder = [
        'data' => [
            "*" => [
                "id",
                "purchase_request_id",
                "purchase_contract_id",
                "supplier_id",
                "supplier_name",
                "supplier_address",
                "supplier_phone",
                "billing_address",
                "billing_phone",
                "billing_email",
                "shipping_address",
                "shipping_phone",
                "shipping_email",
                "warehouse_id",
                "eta",
                "cash_only",
                "need_down_payment",
                "delivery_fee",
                "discount_percent",
                "discount_value",
                "type_of_tax",
                "tax",
                "amount",
                "form" => [
                    "id",
                    "branch_id",
                    "date",
                    "number",
                    "edited_number",
                    "edited_notes",
                    "notes",
                    "created_by",
                    "updated_by",
                    "done",
                    "increment",
                    "increment_group",
                    "formable_id",
                    "formable_type",
                    "request_approval_to",
                    "approval_by",
                    "approval_at",
                    "approval_reason",
                    "approval_status",
                    "request_cancellation_to",
                    "request_cancellation_by",
                    "request_cancellation_at",
                    "request_cancellation_reason",
                    "cancellation_approval_at",
                    "cancellation_approval_by",
                    "cancellation_approval_reason",
                    "cancellation_status",
                    "created_at",
                    "updated_at",
                    "request_close_to",
                    "request_close_by",
                    "request_close_at",
                    "request_close_reason",
                    "close_approval_at",
                    "close_approval_by",
                    "close_status",
                    "request_approval_at",
                    "close_approval_reason",
                ],
                "supplier" => [
                    "id",
                    "code",
                    "tax_identification_number",
                    "name",
                    "address",
                    "city",
                    "state",
                    "country",
                    "zip_code",
                    "latitude",
                    "longitude",
                    "phone",
                    "phone_cc",
                    "email",
                    "notes",
                    "branch_id",
                    "created_by",
                    "updated_by",
                    "archived_by",
                    "created_at",
                    "updated_at",
                    "archived_at",
                    "label",
                ],
                "items" => [
                    "*" => [
                        "id",
                        "purchase_order_id",
                        "purchase_request_item_id",
                        "item_id",
                        "item_name",
                        "quantity",
                        "price",
                        "discount_percent",
                        "discount_value",
                        "taxable",
                        "unit",
                        "converter",
                        "notes",
                        "allocation_id",
                        "item" => [
                            "id",
                            "chart_of_account_id",
                            "code",
                            "barcode",
                            "name",
                            "size",
                            "color",
                            "weight",
                            "notes",
                            "taxable",
                            "require_production_number",
                            "require_expiry_date",
                            "stock",
                            "stock_reminder",
                            "unit_default",
                            "unit_default_purchase",
                            "unit_default_sales",
                            "created_by",
                            "updated_by",
                            "archived_by",
                            "created_at",
                            "updated_at",
                            "archived_at",
                            "label",
                        ],
                        "allocation",
                    ],
                ],
            ],
        ],
        'links' => [
            "first",
            "last",
            "prev",
            "next",
        ],
        'meta' => [
            "current_page",
            "from",
            "last_page",
            "path",
            "per_page",
            "to",
            "total",
        ],
    ];

    /** @test **/
    public function create_purchase_request()
    {
        $items = $this->getItems();

        $item1 = $items[0];
        $item2 = $items[1];

        $data = [
            "approver_email" => $this->user->email,
            "approver_name" => $this->user->full_name,
            "request_approval_to" => $this->user->id,
            "date" => date('Y-m-d H:i:s'),
            "increment_group" => "202210",
            "notes" => "test test",
            "required_date" => date('Y-m-d H:i:s'),
            "items" => [
                [
                    "converter" => 1,
                    "item_id" => $item1->id,
                    "item_name" => $item1->name,
                    "notes" => "test 1",
                    "quantity" => 10,
                    "unit" => "PCS",
                ],
                [
                    "converter" => 1,
                    "item_id" => $item2->id,
                    "item_name" => $item2->name,
                    "notes" => "test 2",
                    "quantity" => 10,
                    "unit" => "PCS",
                ],

            ],
        ];

        // API Request
        $response = $this->json('POST', '/api/v1/purchase/requests', $data, [$this->headers]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();

        // Check Status Response
        $response->assertStatus(201);

        // Check id
        $this->assertTrue($response['data']['id'] > 0);

        // Check items
        $this->assertTrue(is_array($response['data']['items']));

        // Check Database
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $response['data']['id'],
        ], 'tenant');
    }

    /** @test **/
    public function approve_purchase_request()
    {
        $purchaseRequest = $this->getPurchaseRequest();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/requests/{$purchaseRequest->id}/approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseRequest->id);
    }

    /** @test **/
    public function create_purchase_order()
    {
        $supplier = Supplier::first();
        $purchaseRequest = $this->getPurchaseRequest();
        $purchaseRequestItems = PurchaseRequestItem::limit(2)->get();

        $item1 = $purchaseRequestItems[0];
        $item2 = $purchaseRequestItems[1];

        $data = [
            "approver_email" => $this->user->email,
            "approver_name" => $this->user->full_name,
            "request_approval_to" => $this->user->id,
            "cash_only" => false,
            "date" => date('Y-m-d H:i:s'),
            "increment_group" => "202210",
            "discount_percent" => 0,
            "discount_value" => 1000,
            "need_down_payment" => 0,
            "notes" => "test",
            "purchase_request_id" => $purchaseRequest->id,
            "subtotal" => 70000,
            "supplier_address" => null,
            "supplier_id" => $supplier->id,
            "supplier_name" => $supplier->name,
            "supplier_phone" => $supplier->phone,
            "tax" => 6900,
            "tax_base" => 69000,
            "total" => 75900,
            "type_of_tax" => "exclude",
            "items" => [
                [
                    "allocation_id" => null,
                    "converter" => 1,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "item_id" => $item1->item_id,
                    "item_name" => $item1->item_name,
                    "price" => 5000,
                    "purchase_request_item_id" => $item1->id,
                    "quantity" => $item1->quantity,
                    "unit" => $item1->unit,
                ],
                [
                    "allocation_id" => null,
                    "converter" => 1,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "item_id" => $item2->item_id,
                    "item_name" => $item2->item_name,
                    "price" => 2000,
                    "purchase_request_item_id" => $item2->id,
                    "quantity" => $item2->quantity,
                    "unit" => $item2->unit,
                ],

            ],
        ];

        // API Request
        $response = $this->json('POST', '/api/v1/purchase/orders', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check id
        $this->assertTrue($response['data']['id'] > 0);

        // Check items
        $this->assertTrue(is_array($response['data']['items']));

        // Check Database
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $response['data']['id'],
        ], 'tenant');
    }

    /** @test **/
    public function approve_create_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'approval_status' => 1,
        ], 'tenant');
    }

    /** @test **/
    public function reject_create_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/reject", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'approval_status' => -1,
        ], 'tenant');
    }

    /** @test **/
    public function edit_purchase_order()
    {
        $supplier = Supplier::first();
        $purchaseOrder = $this->getPurchaseOrder();
        $purchaseOrderItems = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->limit(2)->get();

        $item1 = $purchaseOrderItems[0];
        $item2 = $purchaseOrderItems[1];

        $data = [
            "id" => $purchaseOrder->id,
            "approver_email" => $this->user->email,
            "approver_name" => $this->user->full_name,
            "request_approval_to" => $this->user->id,
            "cash_only" => $purchaseOrder->cash_only,
            "date" => date('Y-m-d H:i:s'),
            "increment_group" => "202210",
            "discount_percent" => $purchaseOrder->discount_percent,
            "discount_value" => $purchaseOrder->discount_value,
            "need_down_payment" => $purchaseOrder->need_down_payment,
            "notes" => "test edit",
            "purchase_request_id" => $purchaseOrder->purchase_request_id,
            "subtotal" => $purchaseOrder->subtotal,
            "supplier_address" => null,
            "supplier_id" => $supplier->id,
            "supplier_name" => $supplier->name,
            "supplier_phone" => $supplier->phone,
            "tax" => 6900,
            "tax_base" => 69000,
            "type_of_tax" => "exclude",
            "items" => [
                [
                    "allocation_id" => null,
                    "converter" => 1,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "item_id" => $item1->item_id,
                    "item_name" => $item1->item_name,
                    "price" => 5000,
                    "purchase_request_item_id" => $item1->id,
                    "quantity" => $item1->quantity,
                    "unit" => $item1->unit,
                ],
                [
                    "allocation_id" => null,
                    "converter" => 1,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "item_id" => $item2->item_id,
                    "item_name" => $item2->item_name,
                    "price" => 2000,
                    "purchase_request_item_id" => $item2->id,
                    "quantity" => $item2->quantity,
                    "unit" => $item2->unit,
                ],

            ],
        ];

        // API Request
        $response = $this->json('PATCH', "/api/v1/purchase/orders/{$purchaseOrder->id}", $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check id
        $this->assertTrue($response['data']['id'] > 0);

        // Check items
        $this->assertTrue(is_array($response['data']['items']));

        // Check Database
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $response['data']['id'],
        ], 'tenant');
    }

    /** @test **/
    public function approve_edit_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'approval_status' => 1,
        ], 'tenant');
    }

    /** @test **/
    public function reject_edit_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/reject", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'approval_status' => -1,
        ], 'tenant');
    }

    /** @test **/
    public function delete_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        $data = [
            "reason" => "test delete",
        ];

        // API Request
        $response = $this->json('DELETE', "/api/v1/purchase/orders/{$purchaseOrder->id}", $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(204);
    }

    /** @test **/
    public function approve_delete_purchase_order()
    {
        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/cancellation-approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'cancellation_status' => 1,
        ], 'tenant');
    }

    /** @test **/
    public function reject_delete_purchase_order()
    {
        // Create PO
        $this->create_purchase_order();

        // Delete PO
        $this->delete_purchase_order();

        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/cancellation-reject", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Database
        $this->assertDatabaseHas('forms', [
            'formable_type' => 'PurchaseOrder',
            'formable_id' => $purchaseOrder->id,
            'cancellation_status' => -1,
        ], 'tenant');
    }

    /** @test **/
    public function show_purchase_order()
    {
        // Create PO
        $this->create_purchase_order();

        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $url = "/api/v1/purchase/orders/{$purchaseOrder->id}?with_archives=true&with_origin=true&includes=supplier;items.item;items.allocation;purchaseRequest.form;form.createdBy;form.requestApprovalTo;form.branch";
        $response = $this->json('GET', $url, [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseOrder->id);

        // Check Response Structure
        $response->assertJsonStructure($this->jsonShowPurchaseOrder);
    }

    /** @test **/
    public function index_purchase_order()
    {
        $dateStart = date('Y-m-01');
        $dateEnd = date('Y-m-t');
        $url = "/api/v1/purchase/orders?join=form,supplier,items,item&fields=purchase_order.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived;null&filter_like={}&filter_date_min={\"form.date\":\"{$dateStart} 00:00:00\"}&filter_date_max={\"form.date\":\"{$dateEnd} 23:59:59\"}&limit=10&includes=form;supplier;items.item;items.allocation&page=1";

        // Create PO
        $this->create_purchase_order();

        // API Request
        $response = $this->json('GET', $url, [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check Response Structure
        $response->assertJsonStructure($this->jsonIndexPurchaseOrder);
    }
}
