<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderApprovalTest extends TestCase
{
    use DeliveryOrderSetup;

    public static $path = '/api/v1/sales/delivery-orders';

    private $previousDeliveryOrderData;

    /** @test */
    public function success_create_delivery_order($isFirstCreate = true)
    {
        $data = $this->getDummyData();
        
        if($isFirstCreate) {
            $this->setRole();
            $this->previousDeliveryOrderData = $data;
        }

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
    public function unauthorized_approve_delivery_order()
    {
        $this->success_create_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
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
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.sales_order.form.id'),
            'number' => $response->json('data.sales_order.form.number'),
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Approved'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_reject_delivery_order()
    {
        $this->success_create_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_reject_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_reject_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Rejected'
        ], 'tenant');
    }
    
    /** @test */
    public function success_read_approval_delivery_order()
    {
        $this->success_create_delivery_order();

        $data = [
            'join' => 'form,customer,items,item',
            'fields' => 'sales_delivery_order.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form'=>'notArchived;null',
            'filter_like'=>'{}',
            'filter_date_min'=>'{"form.date":"2022-05-01 00:00:00"}',
            'filter_date_max'=>'{"form.date":"2022-05-17 23:59:59"}',
            'includes'=>'form;customer;warehouse;items.item;items.allocation',
            'limit'=>10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/approval', $data, $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function success_send_approval_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['ids'][] = ['id' => $deliveryOrder->id];

        $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function success_send_multiple_approval_delivery_order()
    {
        $this->success_create_delivery_order();

        $this->success_create_delivery_order($isFirstCreate = false);
        $deliveryOrder = DeliveryOrder::orderBy('id', 'desc')->first();
        $deliveryOrder->form->request_approval_to = $this->previousDeliveryOrderData['request_approval_to'];
        $deliveryOrder->form->save();

        $data['ids'] = DeliveryOrder::get()
            ->pluck('id')
            ->map(function ($id) { return ['id' => $id]; })
            ->toArray();

        $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

        $response->assertStatus(200);
    }
}
