<?php

namespace Tests\Feature\Http\Purchase\PurchaseReceive;

use App\Model\Auth\Permission;
use App\Model\Master\Item;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseReceiveControllerTest extends TestCase
{
    use PurchaseReceiveSetup;

    public function createPurchaseReceiveBranchNotDefault()
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
    public function createPurchaseReceiveFailed()
    {
        $this->setRole();

        $dummy = $this->getDummyData();
        $data = ['warehouse_id' => $dummy['warehouse_id'], 'items' => $dummy['items']];

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
            ]);
    }


    /** @test */
    public function createPurchaseReceiveQuantityZero()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data['items'][0] = data_set($data['items'][0], 'quantity', 0);
        $data['items'][0]['dna'][0] = data_set($data['items'][0]['dna'][0], 'quantity', 0);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'total_quantity' => [
                        'quantity must be filled in',
                    ],
                ],
            ]);
    }

    /** @test */
    public function createPurchaseReceive()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'done' => 0,
        ], 'tenant');

        $this->assertDatabaseHas('purchase_receives', [
            "supplier_id" => $response->json('data.supplier_id'),
            "supplier_name" => $response->json('data.supplier_name'),
            "warehouse_id" => $response->json('data.warehouse_id'),
            "warehouse_name" => $response->json('data.warehouse_name'),
            "purchase_order_id" => $response->json('data.purchase_order_id'),
            "id" => $response->json('data.id'),
        ], 'tenant');

        $item = $response->json('data.items')[0]; 
        $this->assertDatabaseHas('purchase_receive_items', [
            "id" => $item['id'],
            "purchase_receive_id" => $item['purchase_receive_id'],
            "purchase_order_item_id" => $item['purchase_order_item_id'],
            "item_id" => $item['item_id'],
            "item_name" => $item['item_name'],
            "quantity" => $item['quantity'],
            "price" => $item['price'],
            "discount_value" => $item['discount_value'],
            "taxable" => $item['taxable'],
            "unit" => $item['unit'],
            "converter" => $item['converter'],
        ], 'tenant');
    }

    /** @test */
    public function updatePurchaseReceive()
    {
        $this->createPurchaseReceive();

        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();

        $data = $this->getDummyData($purchaseReceive);
        $data = data_set($data, 'id', $purchaseReceive->id, false);
        $data['items'][0]['unit'] = 1;

        $response = $this->json('PATCH', self::$path.'/'.$purchaseReceive->id, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', ['edited_number' => $response->json('data.form.number')], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReceive',
            'activity' => 'Update - 1',
        ], 'tenant');
    }

    /** @test */
    public function deletePurchaseReceive()
    {
        $this->createPurchaseReceive();

        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$purchaseReceive->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $purchaseReceive->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function approvalDeletionPurchaseReceiveFormStatusPending()
    {
        $this->deletePurchaseReceive();

        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path.'/'.$purchaseReceive->id.'/cancellation-approve', $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $purchaseReceive->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
    }

    public function createPurchaseInvoice()
    {
        $this->createCustomerUnitItem();
        $this->createPurchaseReceive();
        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();
        $orderItem = $purchaseReceive->items()->first();

        $invoiceData  = [
            'due_date' => now(),
            'date' => now(),
            'increment_group' => 1,
            'delivery_fee' => 0,
            'discount_percent' => 0,
            'discount_value' => 0,
            'type_of_tax' => 'exclude',
            'tax' => 0,
            'invoice_number',
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'items' => [
                [
                    'purchase_receive_id' => $purchaseReceive->id,
                    'purchase_receive_item_id' => $orderItem->id,
                    'item_id' => Item::first()->id,
                    'item_name' => 'test',
                    'quantity' => $orderItem->quantity,
                    'unit' => $orderItem->unit,
                    'price' => $orderItem->price,
                    'converter' => $orderItem->converter
                ],
            ],
        ];

        $response = $this->json('POST', '/api/v1/purchase/invoices', $invoiceData, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function markDonePurchaseReceive()
    {
        $this->createPurchaseInvoice();
        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();
        $this->assertDatabaseHas('forms', [
            'id' => $purchaseReceive->form->id,
            'number' => $purchaseReceive->form->number,
            'done' => 1,
        ], 'tenant');
    }

    /** @test */
    public function deletePurchaseReceiveStatusDone()
    {
        $this->createPurchaseInvoice();
        $purchaseReceive = PurchaseReceive::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$purchaseReceive->id, $data, $this->headers);

        $response->assertStatus(422)
        ->assertJson([
            'code' => 422,
            'message' => 'Cannot edit form because referenced by purchase receive',
        ]);
    }
}
