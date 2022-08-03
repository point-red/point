<?php

namespace Tests\Feature\Http\Sales\DeliveryNote;

use Tests\TestCase;

class DeliveryNoteTest extends TestCase
{
    use DeliveryNoteSetup;

    public static $path = '/api/v1/sales/delivery-notes';

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
    public function createDeliveryNoteStockNotEnough()
    {
        $this->setRole();
        $this->setStock(10);

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(500);
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
}
