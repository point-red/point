<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderCloseApprovalTest extends TestCase
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
    public function invalid_state_close_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $deliveryOrder->form->done = true;
        $deliveryOrder->form->save();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close', $data, $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_close_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close', $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'request_close_reason' => $data['reason'],
            'close_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_close_approve_delivery_order()
    {
        $this->success_close_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_state_close_approve_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-approve', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_close_approve_delivery_order()
    {
        $this->success_close_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'close_status' => 1,
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 1,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_close_reject_delivery_order()
    {
        $this->success_close_delivery_order();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_close_reject_delivery_order()
    {
        $this->success_close_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function invalid_state_close_reject_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-reject', $data, $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_reject_delivery_order()
    {
        $this->success_close_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'close_status' => -1,
            'close_approval_reason' => $data['reason']
        ], 'tenant');
    }
}
