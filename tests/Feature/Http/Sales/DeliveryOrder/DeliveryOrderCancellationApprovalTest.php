<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderCancellationApprovalTest extends TestCase
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
    public function unauthorized_cancellation_approve_delivery_order()
    {
        $this->success_delete_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_state_cancellation_approve_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_cancellation_approve_delivery_order()
    {
        $this->success_delete_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Cancel Approved'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_cancellation_reject_delivery_order()
    {
        $this->success_delete_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_cancellation_reject_delivery_order()
    {
        $this->success_delete_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function invalid_state_cancellation_reject_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-reject', $data, $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_reject_delivery_order()
    {
        $this->success_delete_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/cancellation-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'cancellation_status' => -1,
            'done' => 0
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Cancel Rejected'
        ], 'tenant');
    }
}
