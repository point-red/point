<?php

namespace Tests\Feature\Http\Purchase;

use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use PurchaseOrderSetup, PurchaseOrderTestData;

    /**
     * Test create purchase request to generate sample data for purchase order
     *
     * @return void
     */
    public function test_create_purchase_request()
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

    /**
     * Test purchase request approval
     *
     * @return void
     */
    public function test_approve_purchase_request()
    {
        $purchaseRequest = $this->getPurchaseRequest();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/requests/{$purchaseRequest->id}/approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $response->assertJsonPath('data.id', $purchaseRequest->id);
    }

    /**
     * Test create purchase order using previously generate purchase request
     *
     * @return void
     */
    public function test_create_purchase_order()
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

    /**
     * Test approva new purchase order
     *
     * @return void
     */
    public function test_approve_create_purchase_order()
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

    /**
     * Test reject new purchase order
     *
     * @return void
     */
    public function test_reject_create_purchase_order()
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

    /**
     * Test edit purchase order
     *
     * @return void
     */
    public function test_edit_purchase_order()
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

    /**
     * Test edited purchase order approval
     *
     * @return void
     */
    public function test_approve_edit_purchase_order()
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

    /**
     * Test reject edited purchase order
     *
     * @return void
     */
    public function test_reject_edit_purchase_order()
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

    /**
     * Test deleting purchase order
     *
     * @return void
     */
    public function test_delete_purchase_order()
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

    /**
     * Test delete purchase order approval
     *
     * @return void
     */
    public function test_approve_delete_purchase_order()
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

    /**
     * Test reject delete purchase order
     *
     * @return void
     */
    public function test_reject_delete_purchase_order()
    {
        // Create PO
        $this->test_create_purchase_order();

        // Delete PO
        $this->test_delete_purchase_order();

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

    /**
     * Test show details of purchase order
     *
     * @return void
     */
    public function test_show_purchase_order()
    {
        // Create PO
        $this->test_create_purchase_order();

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

    /**
     * Test purchase order index data
     *
     * @return void
     */
    public function test_index_purchase_order()
    {
        $dateStart = date('Y-m-01');
        $dateEnd = date('Y-m-t');
        $url = "/api/v1/purchase/orders?join=form,supplier,items,item&fields=purchase_order.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived;null&filter_like={}&filter_date_min={\"form.date\":\"{$dateStart} 00:00:00\"}&filter_date_max={\"form.date\":\"{$dateEnd} 23:59:59\"}&limit=10&includes=form;supplier;items.item;items.allocation&page=1";

        // Create PO
        $this->test_create_purchase_order();

        // API Request
        $response = $this->json('GET', $url, [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check Response Structure
        $response->assertJsonStructure($this->jsonIndexPurchaseOrder);
    }
}
