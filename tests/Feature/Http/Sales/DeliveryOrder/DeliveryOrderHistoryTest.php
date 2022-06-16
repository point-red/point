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
    }
    /** @test */
    public function read_delivery_order_histories()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $deliveryOrder->id . '/histories', $data, $this->headers);

        $response->assertStatus(200);
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
    }
}
