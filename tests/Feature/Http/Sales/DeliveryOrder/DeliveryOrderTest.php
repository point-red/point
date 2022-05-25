<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use App\Model\Master\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Model\Auth\Role;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;

class DeliveryOrderTest extends TestCase
{
    public static $path = '/api/v1/sales/delivery-orders';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();

        $this->userWarehouse($this->user);
    }

    private function createSalesOrderApprove()
    {
        $customer = factory(Customer::class)->create();

        $unit = factory(ItemUnit::class)->make();
        $item = factory(Item::class)->create();
        $item->units()->save($unit);

        $role = Role::findByName('super admin', 'api');
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
            "request_approval_to" => 1,
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
    
    private function getDummyData($warehouse)
    {
        $salesOrderApproved = $this->createSalesOrderApprove();
        
        $customer = $salesOrderApproved->customer;
        $approver = $salesOrderApproved->form->request_approval_to;

        $salesOrderItem = $salesOrderApproved->items()->first();
        $item = $salesOrderItem->item;
        $quantityRequested = $salesOrderItem->quantity;
        $quantityDelivered = $salesOrderItem->quantity;
        $quantityRemaining = $quantityRequested - $quantityDelivered;

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
                    "sales_order_item_id" => $salesOrderItem->id,
                    "item_id" => $salesOrderItem->item_id,
                    "item_name" => $salesOrderItem->item_name,
                    "item_label" => "[{$item->code}] - {$item->name}",
                    "more" => false,
                    "unit" => $salesOrderItem->unit,
                    "converter" => $salesOrderItem->converter,
                    "quantity_requested" => $quantityRequested,
                    "quantity_delivered" => $quantityDelivered,
                    "quantity_remaining" => $quantityRemaining,
                    "is_quantity_over" => false,
                    "price" => $salesOrderItem->price,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "total" => $salesOrderApproved->amount,
                    "allocation_id" => null,
                    "notes" => null,
                    "warehouse_id" => $warehouse->id,
                    "warehouse_name" => $warehouse->name
                ]
            ],
            "request_approval_to" => $approver->id,
            "approver_name" => $approver->name,
            "approver_email" => $approver->email,
            "sales_order_id" => $salesOrderApproved->id
        ];
    }

    /** @test */
    public function unauthorized_create_delivery_order()
    {
        // use different warehouse with authorized user
        $warehouse = factory(Warehouse::class)->create();
        $data = $this->getDummyData($warehouse);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        dd($response);

        $response->assertStatus(201);
    }
    /** @test */
    // public function invalid_create_delivery_order()
    // {
    //     $data = $this->getDummyData();

    //     $response = $this->json('POST', self::$path, $data, $this->headers);

    //     $response->assertStatus(201);
    // }
    /** @test */
    // public function can_create_delivery_order()
    // {
    //     $data = $this->getDummyData();

    //     $response = $this->json('POST', self::$path, $data, $this->headers);

    //     $response->assertStatus(201);
    // }
}
