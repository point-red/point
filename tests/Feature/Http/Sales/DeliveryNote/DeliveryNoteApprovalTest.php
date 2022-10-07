<?php

namespace Tests\Feature\Http\Sales\DeliveryNote;

use App\Model\Sales\DeliveryNote\DeliveryNote;
use Tests\TestCase;

class DeliveryNoteApprovalTest extends TestCase
{
    use DeliveryNoteSetup;

    /** @test */
    public function createDeliveryNote()
    {
        $this->setRole();
        $this->generateChartOfAccount();
        $this->setStock(300);

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
    public function updateDeliveryNote()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data = data_set($data, 'request_approval_to', $this->user->id);
        $data['items'][0] = data_set($data['items'][0], 'quantity_remaining', 0);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', ['edited_number' => $response->json('data.form.number')], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Update - 1',
        ], 'tenant');
    }

    /** @test */
    public function ApproveDeliveryNoteUnauthorized()
    {
        $this->updateDeliveryNote();

        $this->unsetUserRole();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 0,
                'message' => 'There is no permission named `approve sales delivery note` for guard `api`.',
            ]);
    }

    /** @test */
    public function ApproveDeliveryNote()
    {
        $this->updateDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $deliveryNote->form->request_approval_to = $this->user->id;
        $deliveryNote->form->save();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.delivery_order.form.id'),
            'number' => $response->json('data.delivery_order.form.number'),
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Approved',
        ], 'tenant');
    }

    /** @test */
    public function rejectDeliveryNoteUnauthorized()
    {
        $this->updateDeliveryNote();

        $this->unsetUserRole();

        $deliveryOrder = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path.'/'.$deliveryOrder->id.'/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 0,
                'message' => 'There is no permission named `approve sales delivery note` for guard `api`.',
            ]);
    }

    /** @test */
    public function rejectDeliveryNoteInvalid()
    {
        $this->updateDeliveryNote();

        $deliveryOrder = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path.'/'.$deliveryOrder->id.'/reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function rejectDeliveryNote()
    {
        $this->updateDeliveryNote();

        $deliveryOrder = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path.'/'.$deliveryOrder->id.'/reject', $data, $this->headers);

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
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Rejected',
        ], 'tenant');
    }
}
