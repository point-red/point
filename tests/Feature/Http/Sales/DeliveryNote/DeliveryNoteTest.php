<?php

namespace Tests\Feature\Http\Sales\DeliveryNote;

use App\Model\Auth\Permission;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Tests\TestCase;

class DeliveryNoteTest extends TestCase
{
    use DeliveryNoteSetup;

    /** @test */
    public function createDeliveryNoteBranchNotDefault()
    {
        $this->setStock(300);
        $this->setRole();

        $data = $this->getDummyData();

        $this->unsetDefaultBranch();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'please set default branch to create this form',
            ]);
    }

    /** @test */
    public function createDeliveryNoteWarehouseNotDefault()
    {
        $this->setRole();

        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'Warehouse  not set as default',
            ]);
    }

    /** @test */
    public function createDeliveryNoteFailed()
    {
        $this->setRole();

        $dummy = $this->getDummyData();
        $data = ['warehouse_id' => $dummy['warehouse_id']];

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
            ]);
    }

    /** @test */
    public function createDeliveryNoteNoPermission()
    {
        Permission::createIfNotExists('create sales delivery note');

        $this->setStock(300);

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'Unauthorized',
            ]);
    }

    /** @test */
    public function createDeliveryNoteStockNotEnough()
    {
        $this->setRole();
        $this->setStock(10);

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'Stock '.$data['items'][0]['item_name'].' not enough',
            ]);
    }

    /** @test */
    public function createDeliveryNoteQuantityMinus()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data['items'][0] = data_set($data['items'][0], 'quantity', -1000);
        $data['items'][0]['dna'] = data_set($data['items'][0]['dna'], 'quantity', -1000);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'items.0.quantity' => [
                        'The items.0.quantity must be at least 1.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function createDeliveryNoteQuantityZero()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data['items'][0] = data_set($data['items'][0], 'quantity', 0);
        $data['items'][0]['dna'] = data_set($data['items'][0]['dna'], 'quantity', 0);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'items.0.quantity' => [
                        'The items.0.quantity must be at least 1.',
                    ],
                ],
            ]);
    }

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
    public function getListDeliveryOrder()
    {
        $this->setRole();

        $data = [
            'join' => 'form,customer,items,item',
            'fields' => 'sales_delivery_note.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"2022-05-01 00:00:00"}',
            'filter_date_max' => '{"form.date":"2022-05-08 23:59:59"}',
            'limit' => 10,
            'includes' => 'form;customer;warehouse;items.item;items.allocation',
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function exportDeliveryNote()
    {
        $this->setRole();

        $data = [
            'join' => 'form,customer,items,item',
            'fields' => 'sales_delivery_note.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"2022-05-01 00:00:00"}',
            'filter_date_max' => '{"form.date":"2022-05-08 23:59:59"}',
            'limit' => 1,
            'includes' => 'form;customer;warehouse;items.item;items.allocation',
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path.'/export', $data, $this->headers);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['url']]);
    }

    /** @test */
    public function exportDeliveryNoteFailed()
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
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path.'/export', $data, $headers);
        $response->assertStatus(500);
    }

    /** @test */
    public function getDeliveryNote()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = [
            'includes' => 'customer;warehouse;items.item;items.allocation;deliveryOrder.form;form.createdBy;form.requestApprovalTo;form.branch',
        ];

        $response = $this->json('GET', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function getDeliveryNoteNotFound()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = [
            'includes' => 'customer;warehouse;items.item;items.allocation;deliveryOrder.form;form.createdBy;form.requestApprovalTo;form.branch',
        ];

        $response = $this->json('GET', self::$path.'/'.($deliveryNote->id + 1), $data, $this->headers);

        $response->assertStatus(404);
    }

    /** @test */
    public function updateDeliveryNoteStockNotEnough()
    {
        $this->createDeliveryNote();
        $this->setStock(10);

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data['items'][0] = data_set($data['items'][0], 'quantity', 1000);
        $data['items'][0]['dna'] = data_set($data['items'][0]['dna'], 'quantity', 1000);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'Stock '.$data['items'][0]['item_name'].' not enough',
            ]);
    }

    /** @test */
    public function updateDeliveryNoteQuantityMinus()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data['items'][0] = data_set($data['items'][0], 'quantity', -1000);
        $data['items'][0]['dna'] = data_set($data['items'][0]['dna'], 'quantity', -1000);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'items.0.quantity' => [
                        'The items.0.quantity must be at least 1.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function updateDeliveryNoteQuantityZero()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data['items'][0] = data_set($data['items'][0], 'quantity', 0);
        $data['items'][0]['dna'] = data_set($data['items'][0]['dna'], 'quantity', 0);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'items.0.quantity' => [
                        'The items.0.quantity must be at least 1.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function updateDeliveryNoteNotFound()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);

        $response = $this->json('PATCH', self::$path.'/'.($deliveryNote->id + 1), $data, $this->headers);

        $response->assertStatus(404);
    }

    /** @test */
    public function updateDeliveryNoteUnauthorized()
    {
        Permission::createIfNotExists('update sales delivery note');

        $this->createDeliveryNote();
        $this->unsetUserRole();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data = $this->getDummyData($deliveryNote);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'Unauthorized',
            ]);
    }

    /** @test */
    public function updateDeliveryNoteInvalidDate()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);

        $actualDate = $data['date'];
        $modifyDate = date('Y-m-d', strtotime('-1 day'));

        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data = data_set($data, 'date', $modifyDate);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'date' => [
                        'The date must be a date after or equal to '.$actualDate.'.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function UpdateDeliveryNoteInvalid()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);
        $data = data_set($data, 'delivery_order_id', null);

        $response = $this->json('PATCH', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
            ]);
    }

    /** @test */
    public function updateDeliveryNote()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($deliveryNote);
        $data = data_set($data, 'id', $deliveryNote->id, false);

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
    public function deleteDeliveryNoteUnauthorized()
    {
        $this->createDeliveryNote();

        $this->unsetUserRole();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$deliveryNote->id, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 0,
                'message' => 'There is no permission named `delete sales delivery note` for guard `api`.',
            ]);
    }

    /** @test */
    public function deleteDeliveryNoteNoReason()
    {
        $this->createDeliveryNote();

        $deliveryNote = DeliveryNote::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$deliveryNote->id, [], $this->headers);

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
}
