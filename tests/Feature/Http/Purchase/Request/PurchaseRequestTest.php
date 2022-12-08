<?php

namespace Tests\Feature\Http\Purchase\Request;
use Illuminate\Testing\Fluent\AssertableJson;
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
        $response->assertStatus(422)->assertJson([
            'code' => 422,
            'message' => 'The given data was invalid.',
        ]);
    }

    /** @test */
    public function failed_default_branch_create_purchase_request()
    {
        $data = $this->createDataPurchaseRequest();
        $this->setDefaultBranch(false);

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            'code' => 422,
            'message' => 'please set default branch to save this form',
        ]);
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
            'required_date' => $this->convertDateTime($this->purchase->required_date)
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

        $data = [
            'join' => 'form,items,item',
            'fields' => 'purchase_request.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"'.date('Y-m-01 00:00:00').'"}',
            'filter_date_max' => '{"form.date":"'.date('Y-m-d 00:00:00').'"}',
            'limit' => 10,
            'includes' => 'form;items.item;',
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);
        // var_dump($response->getContent());
        // $response = $this->json('GET', self::$path.'?join=form,items,item&fields=purchase_request.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_not_null=form.number&%7B%22form.date%22:%22'.date('Y-m-01').'+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d').'+23:59:59%22%7D&limit=10&includes=form%3Bitems.item&page=1', array(), $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function read_all_With_filter_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            'join' => 'form,items,item',
            'fields' => 'purchase_request.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{}',
            'filter_date_min' => '{"form.date":"'.date('Y-m-15 00:00:00').'"}',
            'filter_date_max' => '{"form.date":"'.date('Y-m-16 00:00:00').'"}',
            'limit' => 10,
            'includes' => 'form;items.item;',
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);

        // $response = $this->json('GET', self::$path.'?join=form,items,item&fields=purchase_request.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived%3BapprovalPending&filter_like=%7B%7D&filter_not_null=form.number&%7B%22form.date%22:%22'.date('Y-m-15').'+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d').'+23:59:59%22%7D&limit=10&includes=form%3Bitems.item&page=1', array(), $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function read_all_With_search_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            'join' => 'form,items,item',
            'fields' => 'purchase_request.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived;null',
            'filter_like' => '{"form.number":"'.$this->purchase->form->number.'"}',
            'filter_date_min' => '{"form.date":"'.date('Y-m-15 00:00:00').'"}',
            'filter_date_max' => '{"form.date":"'.date('Y-m-16 00:00:00').'"}',
            'limit' => 10,
            'includes' => 'form;items.item;',
            'page' => 1,
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);
        // $response = $this->json('GET', self::$path.'?join=form,items,item&fields=purchase_request.*&sort_by=-form.number&group_by=form.id&filter_form=notArchived%3BapprovalPending&filter_like=%7B%22form.number%22:%22'.$this->purchase->form->number.'%22,%22item.code%22:%22'.$this->purchase->form->number.'%22,%22item.name%22:%22'.$this->purchase->form->number.'%22,%22purchase_request_item.notes%22:%22'.$this->purchase->form->number.'%22,%22purchase_request_item.quantity%22:%22'.$this->purchase->form->number.'%22%7D&filter_not_null=form.number&%7B%22form.date%22:%22'.date('Y-m-01').'+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d').'+23:59:59%22%7D&limit=10&includes=form%3Bitems.item&page=1', array(), $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function failed_not_same_branch_read_single_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $this->unsetBranch();

        $response = $this->json('GET', self::$path.'/'.$this->purchase->id.'?includes=items.item;items.allocation;form.requestApprovalTo;form.branch&with_archives=true&with_origin=true', array(), $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Unauthorized"
        ]);
    }

    /** @test */
    public function failed_access_read_single_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        // toggle permission
        $data = [
            "permission_name" => "read purchase request",
            "role_id" => $this->role->id
        ];
        $response = $this->json('PATCH', '/api/v1/master/roles/'.$this->role->id.'/permissions', $data, $this->headers);
        $this->assertFalse($this->tenantUser->hasPermissionTo('read purchase request'));

        $response = $this->json('GET', self::$path.'/'.$this->purchase->id.'?includes=items.item;items.allocation;form.requestApprovalTo;form.branch&with_archives=true&with_origin=true', array(), $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Unauthorized"
        ]);
    }

    /** @test */
    public function success_read_single_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('GET', self::$path.'/'.$this->purchase->id.'?includes=items.item;items.allocation;form.requestApprovalTo;form.branch&with_archives=true&with_origin=true', array(), $this->headers);
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
        $response->assertStatus(422)->assertJson([
            'code' => 422,
            'message' => 'The given data was invalid.',
        ]);
    }

    /** @test */
    public function failed_update_purchase_request_linked_puchase_order()
    {
        // $this->expectOutputString('');
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $data = $this->createDataPurchaseRequest();
        $data['id'] = $this->purchase->id;
        $data['required_date'] = date('Y-m-30 H:m:s');

        // link to purchase order
        $this-> createPurchaseOrder($this->purchase);
        
        $response = $this->json('PATCH', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Cannot edit form because referenced by purchase order"
        ]);
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
    public function success_update_purchase_request_with_different_user()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        // login with different user
        $this->setupUser();

        $data = $this->createDataPurchaseRequest();
        $data['id'] = $this->purchase->id;
        $data['required_date'] = date('Y-m-30 H:m:s');

        $response = $this->json('PATCH', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(201);

        $this->assertDatabaseHas('forms', [
            'created_by' => $this->user->id,
        ], 'tenant');
    }

    /** @test */
    public function failed_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, [], $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function failed_password_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'password' => 'wrongPassword',
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Unauthorized"
        ]);
    }

    /** @test */
    public function failed_default_branch_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $this->setDefaultBranch(false);

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'password' => $this->userPassword,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Please set as default branch"
        ]);
    }

    /** @test */
    public function failed_delete_purchase_request_no_access()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $this->role->revokePermissionTo("delete purchase request");

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'password' => $this->userPassword,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        // var_dump($response->getContent());
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Unauthorized"
        ]);
    }

    /** @test */
    public function success_delete_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'password' => $this->userPassword,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(204);

        $this->assertDatabaseHas('forms', [
            'number' => $this->purchase->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
    }

    /** @test */
    public function failed_delete_purchase_request_with_other_user_no_access()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $user = $this->user;
        $this->role->revokePermissionTo("delete purchase request");
        // login with different user
        $this->setupUser(true);
        $this->role->revokePermissionTo("delete purchase request");

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'request_cancellation_to' => $user->id,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "Unauthorized"
        ]);
    }

    /** @test */
    public function success_delete_purchase_request_with_other_user()
    {
        // $this->expectOutputString("");
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $user = $this->user;
        // login with different user
        $this->setupUser(true);
        $this->role->revokePermissionTo("delete purchase request");

        $data = [
            'id' => $this->purchase->id,
            'tenant_url' => 'http://dev.localhost:8080',
            'request_cancellation_to' => $user->id,
            'reason' => 'Reason'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$this->purchase->id, $data, $this->headers);
        // var_dump($response->getContent());
        $response->assertStatus(204);

        $this->assertDatabaseHas('forms', [
            'number' => $this->purchase->form->number,
            'request_cancellation_to' => $user->id,
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_delete_purchase_request()
    {
        $this->success_delete_purchase_request();

        $data = [
            'id' => $this->purchase->id
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/cancellation-approve', $data, $this->headers);
        $response->assertStatus(200);
    }

    /** @test */
    public function failed_reject_delete_purchase_request()
    {
        $this->success_delete_purchase_request();
        
        $data = [
            'id' => $this->purchase->id
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/cancellation-reject', $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function success_reject_delete_purchase_request()
    {
        $this->success_delete_purchase_request();
        
        $data = [
            'id' => $this->purchase->id,
            'reason' => 'reason'
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/cancellation-reject', $data, $this->headers);
        $response->assertStatus(200);
    }
}