<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderHistoryTest extends TestCase
{
    use DeliveryOrderSetup;

    public static $path = '/api/v1/sales/delivery-orders';

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
    public function read_delivery_order_histories()
    {
        $this->success_update_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $deliveryOrderUpdated = DeliveryOrder::orderBy('id', 'desc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $deliveryOrderUpdated->id . '/histories', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->edited_number,
            'table_id' => $deliveryOrder->id,
            'table_type' => $deliveryOrder::$morphName,
            'activity' => 'Created'
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrderUpdated->form->number,
            'table_id' => $deliveryOrderUpdated->id,
            'table_type' => $deliveryOrderUpdated::$morphName,
            'activity' => 'Update - 1'
        ], 'tenant');
    }
    /** @test */
    public function success_create_delivery_order_history()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data = [
            "id" => $deliveryOrder->id,
            "activity" => "Printed"
        ];

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/histories', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.number'),
            'table_id' => $response->json('data.table_id'),
            'table_type' => $response->json('data.table_type'),
            'activity' => $response->json('data.activity')
        ], 'tenant');
    }
}
