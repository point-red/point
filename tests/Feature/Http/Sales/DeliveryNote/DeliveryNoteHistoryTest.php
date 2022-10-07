<?php

namespace Tests\Feature\Http\Sales\DeliveryNote;

use App\Model\Sales\DeliveryNote\DeliveryNote;
use Tests\TestCase;

class DeliveryNoteHistoryTest extends TestCase
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
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Created',
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
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Update - 1',
        ], 'tenant');
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
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryNote->form->number,
            'table_id' => $deliveryNote->id,
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Approved',
        ], 'tenant');
    }

    /** @test */
    public function rejectDeliveryNote()
    {
        $this->updateDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path.'/'.$deliveryNote->id.'/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Rejected',
        ], 'tenant');
    }

    /** @test */
    public function deleteDeliveryNote()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryNote->form->number,
            'table_id' => $deliveryNote->id,
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Canceled',
        ], 'tenant');
    }

    /** @test */
    public function approveCancelDeliveryNote()
    {
        $this->deleteDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/cancellation-approve', [], $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Cancel Approved',
        ], 'tenant');
    }

    /** @test */
    public function rejectCancelDeliveryNote()
    {
        $this->deleteDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path.'/'.$deliveryNote->id.'/cancellation-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryNote',
            'activity' => 'Cancel Rejected',
        ], 'tenant');
    }

    /** @test */
    public function printDeliveryNoteHistory()
    {
        $this->createDeliveryNote();

        $deliveryOrder = DeliveryNote::orderBy('id', 'asc')->first();
        $data = [
            'id' => $deliveryOrder->id,
            'activity' => 'Printed',
        ];

        $response = $this->json('POST', self::$path.'/'.$deliveryOrder->id.'/histories', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.number'),
            'table_id' => $response->json('data.table_id'),
            'table_type' => $response->json('data.table_type'),
            'activity' => $response->json('data.activity'),
        ], 'tenant');
    }

    /** @test */
    public function getDeliveryNoteHistories()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'desc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path.'/'.$deliveryNote->id.'/histories', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryNote->form->number,
            'table_id' => $deliveryNote->id,
            'table_type' => $deliveryNote::$morphName,
            'activity' => 'Created',
        ], 'tenant');
    }
}
