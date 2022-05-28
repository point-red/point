<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use App\Model\Master\Customer;
use Tests\TestCase;

use App\Model\Auth\Role;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesOrder\SalesOrder;

class DeliveryOrderTest extends TestCase
{
    public static $path = '/api/v1/sales/delivery-orders';

    private $tenantUser;
    private $branchDefault;
    private $warehouseSelected;

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();

        $this->tenantUser = TenantUser::find($this->user->id);
        $this->branchDefault = $this->tenantUser->branches()
                ->where('is_default', true)
                ->first();

        $this->setUserWarehouse($this->branchDefault);
    }
    
    private function setUserWarehouse($branch = null)
    {
        $warehouse = $this->createWarehouse($branch);
        $this->tenantUser->warehouses()->syncWithoutDetaching($warehouse->id);
        foreach ($this->tenantUser->warehouses as $warehouse) {
            $warehouse->pivot->is_default = true;
            $warehouse->pivot->save();

            $this->warehouseSelected = $warehouse;
        }
    }

    protected function unsetUserRole()
    {
        $role = \App\Model\Auth\Role::createIfNotExists('super admin');
        $hasRole = \App\Model\Auth\ModelHasRole::where('role_id', $role->id)
            ->where('model_type', 'App\Model\Master\User')
            ->where('model_id', $this->user->id)
            ->delete();
    }

    private function createWarehouse($branch = null)
    {
        $warehouse = new Warehouse();
        $warehouse->name = 'Test warehouse';

        if($branch) $warehouse->branch_id = $branch->id;

        $warehouse->save();

        return $warehouse;
    }

    private function createSalesOrderApprove()
    {
        $customer = factory(Customer::class)->create();

        $unit = factory(ItemUnit::class)->make();
        $item = factory(Item::class)->create();
        $item->units()->save($unit);

        $role = Role::createIfNotExists('super admin');
        $approver = factory(TenantUser::class)->create();
        $approver->assignRole($role);

        $params = [
            "increment_group" => date("Ym"),
            "date" => date("Y-m-d H:i:s"),
            "customer_id" => $customer->id,
            "customer_name" => $customer->name,
            "customer_label" => $customer->code,
            "customer_address" => $customer->address,
            "customer_phone" => $customer->phone,
            "pricing_group_id" => 1,
            "need_down_payment" => 0,
            "cash_only" => false,
            "notes" => null,
            "discount_percent" => 0,
            "discount_value" => 0,
            "tax_base" => 1100000,
            "tax" => 110000,
            "type_of_tax" => "exclude",
            "items" => [
                [
                    "sales_quotation_item_id" => null,
                    "item_id" => $item->id,
                    "item_name" => $item->name,
                    "more" => false,
                    "unit" => $unit->label,
                    "converter" => 1,
                    "quantity" => "220",
                    "price" => 5000,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "allocation_id" => null,
                    "allocation_name" => "",
                    "notes" => null,
                    "item_label" => "[{$item->code}] - {$item->name}",
                    "units" => [
                        [
                            "id" => $unit->id,
                            "label" => $unit->label,
                            "name" => $unit->name,
                            "converter" => 1,
                            "disabled" => 0,
                            "item_id" => $item->id,
                            "created_by" => 1,
                            "updated_by" => 1,
                            "created_at" => "2022-05-13 10:38:42",
                            "updated_at" => "2022-05-13 10:38:42",
                            "prices" => []
                        ]
                    ]
                ]
            ],
            "request_approval_to" => $approver->id,
            "approver_name" => $approver->getFullNameAttribute(),
            "approver_email" => $approver->email,
            "sales_quotation_id" => null,
            "subtotal" => 1100000,
            "total" => 1210000
        ];

        $salesOrder = SalesOrder::create($params);
        $salesOrder->form->approval_by = $approver->id;
        $salesOrder->form->approval_at = now();
        $salesOrder->form->approval_status = 1;
        $salesOrder->form->save();
        
        return $salesOrder;
    }
    
    private function getDummyData($deliveryOrder = null)
    {
        $warehouse = $this->warehouseSelected;
        $order = $deliveryOrder ?? $this->createSalesOrderApprove();
        
        $customer = $order->customer;
        $approver = $order->form->requestApprovalTo;
        
        $orderItem = $order->items()->first();
        $item = $orderItem->item;

        $salesOrder = $order instanceof DeliveryOrder ? $order->salesOrder : $order;
        $salesOrderItemId = $order instanceof DeliveryOrder ? $orderItem->sales_order_item_id : $orderItem->id;
        $quantityRequested = $order instanceof DeliveryOrder ? $orderItem->quantity_requested : $orderItem->quantity;
        $quantityDelivered = $order instanceof DeliveryOrder ? $orderItem->quantity_delivered : $orderItem->quantity;
        $quantityRemaining = $order instanceof DeliveryOrder ? $orderItem->quantity_remaining : $quantityRequested - $quantityDelivered;

        return [
            "increment_group" => date("Ym"),
            "date" => date("Y-m-d H:i:s"),
            "warehouse_id" => $warehouse->id,
            "warehouse_name" => $warehouse->name,
            "customer_id" => $customer->id,
            "customer_name" => $customer->name,
            "customer_label" => $customer->code,
            "customer_address" => null,
            "customer_phone" => null,
            "customer_email" => null,
            "pricing_group_id" => 1,
            "need_down_payment" => 0,
            "cash_only" => false,
            "notes" => null,
            "discount_percent" => 0,
            "discount_value" => 0,
            "type_of_tax" => "exclude",
            "items" => [
                [
                    "sales_order_item_id" => $salesOrderItemId,
                    "item_id" => $orderItem->item_id,
                    "item_name" => $orderItem->item_name,
                    "item_label" => "[{$item->code}] - {$item->name}",
                    "more" => false,
                    "unit" => $orderItem->unit,
                    "converter" => $orderItem->converter,
                    "quantity_requested" => $quantityRequested,
                    "quantity_delivered" => $quantityDelivered,
                    "quantity_remaining" => $quantityRemaining,
                    "is_quantity_over" => false,
                    "price" => $orderItem->price,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "total" => $order->amount,
                    "allocation_id" => null,
                    "notes" => null,
                    "warehouse_id" => $warehouse->id,
                    "warehouse_name" => $warehouse->name
                ]
            ],
            "request_approval_to" => $approver->id,
            "approver_name" => $approver->name,
            "approver_email" => $approver->email,
            "sales_order_id" => $salesOrder->id
        ];
    }

    /** @test */
    public function unauthorized_create_delivery_order()
    {
        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `create sales delivery order` for guard `api`."
            ]);
    }
    /** @test */
    public function overquantity_create_delivery_order()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data = data_set($data, 'items.0.quantity_delivered', 1000);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Delivery order item can't exceed sales order request"
            ]);
    }
    /** @test */
    public function invalid_create_delivery_order()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data = data_set($data, 'sales_order_id', null);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422);
    }
    /** @test */
    public function success_create_delivery_order()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
    }
    /** @test */
    public function read_all_delivery_order()
    {
        $this->setRole();

        $data = [
            'join' => 'form,customer,items,item',
            'fields' => 'sales_delivery_order.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"2022-05-01 00:00:00"}',
            'filter_date_max' => '{"form.date":"2022-05-08 23:59:59"}',
            'limit' => 10,
            'includes' => 'form;customer;warehouse;items.item;items.allocation',
            'page' => 1
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);

        $response->assertStatus(200);
    }
    /** @test */
    public function read_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $data = [
            'with_archives' => 'true',
            'with_origin' => 'true',
            'includes' => 'customer;warehouse;items.item;items.allocation;salesOrder.form;form.createdBy;form.requestApprovalTo;form.branch'
        ];

        $response = $this->json('GET', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(200);
    }
    /** @test */
    public function unauthorized_update_delivery_order()
    {
        $this->success_create_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($deliveryOrder);

        $response = $this->json('PATCH', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `update sales delivery order` for guard `api`."
            ]);
    }
    /** @test */
    public function overquantity_update_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($deliveryOrder);
        $data = data_set($data, 'id', $deliveryOrder->id, false);
        $data = data_set($data, 'items.0.quantity_delivered', 1000);

        $response = $this->json('PATCH', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Delivery order item can't exceed sales order request"
            ]);
    }
    /** @test */
    public function invalid_update_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($deliveryOrder);
        $data = data_set($data, 'id', $deliveryOrder->id, false);
        $data = data_set($data, 'sales_order_id', null);

        $response = $this->json('PATCH', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(422);
    }
    /** @test */
    public function success_update_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($deliveryOrder);
        $data = data_set($data, 'id', $deliveryOrder->id, false);

        $response = $this->json('PATCH', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(201);
    }
}
