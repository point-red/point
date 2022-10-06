<?php

namespace Tests\Feature\Http\Sales\DeliveryNote;

use App\Model\Sales\DeliveryNote\DeliveryNote;
use Tests\TestCase;

class DeliveryNoteCancellationApprovalTest extends TestCase
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
    public function deleteDeliveryNote()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryNote->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function approveCancelDeliveryNoteUnauthorized()
    {
        $this->deleteDeliveryNote();

        $this->unsetUserRole();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/cancellation-approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 0,
                'message' => 'There is no permission named `approve sales delivery note` for guard `api`.',
            ]);
    }

    /** @test */
    public function approveCancelDeliveryNoteInvalid()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/cancellation-approve', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function approveCancelDeliveryNote()
    {
        $this->deleteDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/cancellation-approve', [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function rejectCancelDeliveryNoteUnauthorized()
    {
        $this->deleteDeliveryNote();

        $this->unsetUserRole();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', DeliveryNoteTest::$path.'/'.$deliveryNote->id.'/cancellation-reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 0,
                'message' => 'There is no permission named `approve sales delivery note` for guard `api`.',
            ]);
    }

    /** @test */
    public function rejectCancelDeliveryNoteInvalid()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path.'/'.$deliveryNote->id.'/cancellation-reject', $data, $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function rejectCancelDeliveryNoteNoReason()
    {
        $this->deleteDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path.'/'.$deliveryNote->id.'/cancellation-reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'reason' => [
                        'The reason field is required.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function rejectCancelDeliveryNote()
    {
        $this->deleteDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path.'/'.$deliveryNote->id.'/cancellation-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'cancellation_status' => -1,
            'done' => 0,
        ], 'tenant');
    }
}
