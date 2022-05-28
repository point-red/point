<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderTest extends TestCase
{
    use DeliveryOrderSetup;

    public static $path = '/api/v1/sales/delivery-orders';

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
