<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;

class DeliveryOrderTest extends TestCase
{
    use DeliveryOrderSetup;

    public static $path = '/api/v1/sales/delivery-orders';

    private function createDeliveryNote($deliveryOrder)
    {
        $warehouse = $this->warehouseSelected;
        
        $customer = $deliveryOrder->customer;
        $approver = $deliveryOrder->form->requestApprovalTo;
        
        $deliveryOrderItem = $deliveryOrder->items()->first();

        $item = $deliveryOrderItem->item;

        $params = [
            "increment_group" => date("Ym"),
            "date" => date("Y-m-d H:i:s"),
            "customer_id" => $customer->id,
            "customer_name" => $customer->name,
            "customer_label" => $customer->code,
            "customer_address" => $customer->address,
            "customer_phone" => $customer->phone,
            "warehouse_id" => $warehouse->id,
            "warehouse_name" => $warehouse->name,
            "pricing_group_id" => 1,
            "need_down_payment" => 0,
            "cash_only" => false,
            "notes" => null,
            "discount_percent" => 0,
            "discount_value" => 0,
            "driver" => "Wawan",
            "license_plate" => "L8765WR",
            "tax_base" => 1100000,
            "tax" => 110000,
            "type_of_tax" => "exclude",
            "items" => [
                [
                    "allocation_id" => null,
                    "allocation_name" => "",
                    "converter" => 1,
                    "discount_percent" => 0,
                    "discount_value" => 0,
                    "delivery_order_item_id" => $deliveryOrderItem->id,
                    "item_id" => $item->id,
                    "item_label" => "[{$item->code}] - {$item->name}",
                    "item_name" => $item->name,
                    "more" => false,
                    "notes" => null,
                    "price" => 5000,
                    "quantity" => "220",
                    "total" => null,
                    "unit" => $deliveryOrderItem->unit,
                    "warehouse_id" => $warehouse->id,
                    "warehouse_name" => $warehouse->name
                ]
            ],
            "request_approval_to" => $approver->id,
            "approver_name" => $approver->getFullNameAttribute(),
            "approver_email" => $approver->email,
            "delivery_order_id" => $deliveryOrder->id,
            "subtotal" => 1100000,
            "total" => 1210000
        ];

        $deliveryNote = new DeliveryNote();
        $deliveryNote->fill($params);

        $deliveryNote->customer_id = $deliveryOrder->customer_id;
        $deliveryNote->customer_name = $deliveryOrder->customer_name;
        $deliveryNote->billing_address = $deliveryOrder->billing_address;
        $deliveryNote->billing_phone = $deliveryOrder->billing_phone;
        $deliveryNote->billing_email = $deliveryOrder->billing_email;
        $deliveryNote->shipping_address = $deliveryOrder->shipping_address;
        $deliveryNote->shipping_phone = $deliveryOrder->shipping_phone;
        $deliveryNote->shipping_email = $deliveryOrder->shipping_email;

        $deliveryNote->save();

        $sourceItems = $params['items'] ?? [];
        $deliveryOrderItems = $deliveryOrder->items;
        $deliveryNoteItems = array_map(function ($item) use ($deliveryOrderItems) {
            $deliveryOrderItem = $deliveryOrderItems->firstWhere('id', $item['delivery_order_item_id']);

            $deliveryNoteItem = new DeliveryNoteItem();
            $deliveryNoteItem->fill($item);
            
            $deliveryNoteItem->item_id = $deliveryOrderItem->item_id;
            $deliveryNoteItem->item_name = $deliveryOrderItem->item_name;
            $deliveryNoteItem->price = $deliveryOrderItem->price;
            $deliveryNoteItem->discount_percent = $deliveryOrderItem->discount_percent;
            $deliveryNoteItem->discount_value = $deliveryOrderItem->discount_value;
            $deliveryNoteItem->taxable = $deliveryOrderItem->taxable;
            $deliveryNoteItem->allocation_id = $deliveryOrderItem->allocation_id;

            return $deliveryNoteItem;
        }, $sourceItems);

        $deliveryNote->items()->saveMany($deliveryNoteItems);

        $form = new Form;
        $form->saveData($params, $deliveryNote);

        $deliveryOrder->updateStatus();

        return $deliveryNote;
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
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }
    /** @test */
    public function success_approve_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_by' => $response->json('data.form.approval_by'),
            'approval_status' => 1,
        ], 'tenant');
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
        $this->success_approve_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $this->createDeliveryNote($deliveryOrder);

        $data = [
            'with_archives' => 'true',
            'with_origin' => 'true',
            'remaining_info' => 'true',
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
        $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Update - 1'
        ], 'tenant');
    }
    /** @test */
    public function unauthorized_delete_delivery_order()
    {
        $this->success_create_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `delete sales delivery order` for guard `api`."
            ]);
    }
    /** @test */
    public function success_delete_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }
    /** @test */
    public function failed_export_delivery_order()
    {
        $this->setRole();

        $headers = $this->headers;
        unset($headers['Tenant']);

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

        $response = $this->json('GET', self::$path . '/export', $data, $headers);
        $response->assertStatus(500);
    }
    /** @test */
    public function success_export_delivery_order()
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

        $response = $this->json('GET', self::$path . '/export', $data, $this->headers);

        $response->assertStatus(200)->assertJsonStructure([ 'data' => ['url'] ]);
    }
}
