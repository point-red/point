<?php

namespace Tests\Feature\Http\Purchase\Request;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use PurchaseRequestSetup;

    /** @test */
    public function failed_create_purchase_request()
    {
        $data = [
            "increment_group" => date('Ym'),
            "date" => date('Y-m-d H:m:s'),
            "required_date" => date('Y-m-d H:m:s'),
            "notes" => "Test Note",
            "items" => []
        ];

        // $data = $this->createDataPurchaseRequest();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);
        // $response->dump();
        $response->assertStatus(422);
    }

    /** @test */
    public function success_create_purchase_request()
    {
        $data = $this->createDataPurchaseRequest();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        // save data
        $this->purchase = json_decode($response->getContent())->data;

        // assert status
        $response->assertStatus(201);
        // assert database
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->purchase->id,
            'required_date' => date('Y-m-d H:m:s', strtotime($this->purchase->required_date.' -7 hour'))
        ], 'tenant');
        $this->assertDatabaseHas('purchase_request_items', [
            'id' => $this->purchase->items[0]->id,
            'purchase_request_id' => $this->purchase->id,
            'item_id' => $data['items'][0]['item_id'],
            'item_name' => $data['items'][0]['item_name'],
            'quantity' => $data['items'][0]['quantity'],
            'quantity_remaining' => $data['items'][0]['quantity_remaining'],
            'unit' => $data['items'][0]['unit'],
            'converter' => $data['items'][0]['converter'],
            'allocation_id' => $data['items'][0]['allocation_id'],
            'notes' => $data['items'][0]['notes'],
        ], 'tenant');
    }

    /** @test */
    public function read_all_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('GET', self::$path.'?join=form,items,item&fields=purchase_request.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_not_null=form.number&%7B%22form.date%22:%22'.date('Y-m-01').'+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d').'+23:59:59%22%7D&limit=10&includes=form%3Bitems.item&page=1', array(), $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function read_single_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('GET', self::$path.'/'.$this->purchase->id.'?includes=items.item;items.allocation;form.requestApprovalTo;form.branch', array(), $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function failed_update_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            "increment_group" => date('Ym'),
            "date" => date('Y-m-d H:m:s'),
            "required_date" => date('Y-m-d H:m:s'),
            "notes" => "Test Note",
            "items" => []
        ];

        // $data = $this->createDataPurchaseRequest();
        
        $response = $this->json('PATCH', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        // $response->dump();
        $response->assertStatus(422);
    }

    /** @test */
    public function success_update_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $data = $this->createDataPurchaseRequest();
        $data['id'] = $this->purchase->id;
        $data['required_date'] = date('Y-m-30 H:m:s');

        $response = $this->json('PATCH', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(201);

        // save data
        $this->purchase = json_decode($response->getContent())->data;

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->purchase->id,
            'required_date' => date('Y-m-d H:m:s', strtotime($data['required_date'].' -7 hour'))
        ], 'tenant');
        $this->assertDatabaseHas('purchase_request_items', [
            'id' => $this->purchase->items[0]->id,
            'purchase_request_id' => $this->purchase->id,
            'item_id' => $data['items'][0]['item_id'],
            'item_name' => $data['items'][0]['item_name'],
            'quantity' => $data['items'][0]['quantity'],
            'quantity_remaining' => $data['items'][0]['quantity_remaining'],
            'unit' => $data['items'][0]['unit'],
            'converter' => $data['items'][0]['converter'],
            'allocation_id' => $data['items'][0]['allocation_id'],
            'notes' => $data['items'][0]['notes'],
        ], 'tenant');
    }

    /** @test */
    public function failed_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, [], $this->headers);
        $response->assertStatus(422);
    }

    /** @test */
    public function failed_default_branch_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $this->setDefaultBranch(false);

        $data = [
            'id' => $this->purchase->id,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(422);
    }

    /** @test */
    public function success_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        /* s: request cancellation test */
        $data = [
            'id' => $this->purchase->id,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(204);
        /* e: request cancellation test */
    }

    /** @test */
    public function success_approve_delete_purchase_request()
    {
        $this->success_delete_purchase_request();

        /* s: cancellation approve test */
        $data = [
            'id' => $this->purchase->id
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/cancellation-approve', $data, $this->headers);
        $response->assertStatus(200);
        /* e: cancellation approve test */
    }

    /** @test */
    public function success_reject_delete_purchase_request()
    {
        $this->success_delete_purchase_request();
        
        /* s: cancellation approve test */
        $data = [
            'id' => $this->purchase->id
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/cancellation-reject', $data, $this->headers);
        $response->assertStatus(200);
        /* e: cancellation approve test */
    }
}