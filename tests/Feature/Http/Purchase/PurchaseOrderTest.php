<?php

namespace Tests\Feature\Http\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    private $permissionsSetup = [
        'create purchase request',
    ];

    private $roleSetup = [
        'super admin',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('tenant:seed:dummy', ['db_name' => env('DB_TENANT_DATABASE')]);

        $this->signIn();
        $this->setProject();

        foreach ($this->permissionsSetup as $permission) {
            $this->createPermission($permission);
        }

        foreach ($this->roleSetup as $role) {
            $this->createRole($role);
        }
    }

    protected function createPermission(string $permission)
    {
        $permission = \App\Model\Auth\Permission::createIfNotExists($permission);
        $hasPermission = new \App\Model\Auth\ModelHasPermission();
        $hasPermission->permission_id = $permission->id;
        $hasPermission->model_type = 'App\Model\Master\User';
        $hasPermission->model_id = $this->user->id;
        $hasPermission->save();
    }

    protected function createRole(string $role)
    {
        $role = \App\Model\Auth\Role::createIfNotExists($role);
        $hasRole = new \App\Model\Auth\ModelHasRole();
        $hasRole->role_id = $role->id;
        $hasRole->model_type = 'App\Model\Master\User';
        $hasRole->model_id = $this->user->id;
        $hasRole->save();
    }

    protected function getPurchaseRequest()
    {
        $form = Form::where('formable_type', 'PurchaseRequest')
            ->orderBy('created_at', 'desc')
            ->first();

        return PurchaseRequest::find($form->formable_id);
    }

    protected function getPurchaseOrder()
    {
        $form = Form::where('formable_type', 'PurchaseOrder')
            ->orderBy('created_at', 'desc')
            ->first();

        return PurchaseOrder::find($form->formable_id);
    }

    /** @test **/
    public function create_purchase_request()
    {
        $item1 = Item::find(1);
        $item2 = Item::find(2);

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
        $this->assertTrue($response['data']['id'] == $purchaseRequest->id);
    }

    /** @test **/
    public function create_purchase_order()
    {
        $supplier = Supplier::find(1);
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
        $this->assertTrue($response['data']['id'] == $purchaseOrder->id);
    }

    public function edit_purchase_order()
    {
        $supplier = Supplier::find(1);
        $purchaseOrder = $this->getPurchaseOrder();
        $purchaseOrderItems = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->limit(2)->get();

        $item1 = $purchaseOrderItems[0];
        $item2 = $purchaseOrderItems[1];

        $data = [
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

    public function approve_edit_purchase_order()
    {

        $purchaseOrder = $this->getPurchaseOrder();

        // API Request
        $response = $this->json('POST', "/api/v1/purchase/orders/{$purchaseOrder->id}/approve", [], [$this->headers]);

        // Check Status Response
        $response->assertStatus(200);

        // Check id
        $this->assertTrue($response['data']['id'] == $purchaseOrder->id);
    }
}
