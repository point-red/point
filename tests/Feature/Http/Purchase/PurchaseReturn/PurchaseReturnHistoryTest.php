<?php

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;

class PurchaseReturnHistoryTest extends TestCase
{
    use PurchaseReturnSetup;

    public static $path = '/api/v1/purchase/return';

    /** @test */
    public function success_create_purchase_return()
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
    public function success_update_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($purchaseReturn);
        $data = data_set($data, 'id', $purchaseReturn->id, false);

        $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Update - 1'
        ], 'tenant');
    }

    /** @test */
    public function read_purchase_return_histories()
    {
        $this->success_update_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $purchaseReturnUpdated = PurchaseReturn::orderBy('id', 'desc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $purchaseReturnUpdated->id . '/histories', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturn->form->edited_number,
            'table_id' => $purchaseReturn->id,
            'table_type' => $purchaseReturn::$morphName,
            'activity' => 'Created'
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturnUpdated->form->number,
            'table_id' => $purchaseReturnUpdated->id,
            'table_type' => $purchaseReturnUpdated::$morphName,
            'activity' => 'Update - 1'
        ], 'tenant');
    }

    /** @test */
    public function success_create_purchase_return_history()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data = [
            "id" => $purchaseReturn->id,
            "activity" => "Printed"
        ];

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/histories', $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.number'),
            'table_id' => $response->json('data.table_id'),
            'table_type' => $response->json('data.table_type'),
            'activity' => $response->json('data.activity')
        ], 'tenant');
    }

    /** @test */
  public function unauthorized_read_purchase_return_history()
  {   
      
      $this->success_update_purchase_return();
      $this->unsetUserRole();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $purchaseReturnUpdated = PurchaseReturn::orderBy('id', 'desc')->first();

      $data = [
          'sort_by' => '-user_activities.date',
          'includes' => 'user',
          'filter_like' => '{}',
          'or_filter_where_has_like[]' => '{"user":{}}',
          'limit' => 10,
          'page' => 1
      ];

      $response = $this->json('GET', self::$path . '/' . $purchaseReturnUpdated->id . '/histories', $data, $this->headers);
      
      $response->assertStatus(500)
      ->assertJson([
          "code" => 0,
          "message" => "There is no permission named `read purchase return` for guard `api`."
      ]);
  }

  /** @test */
  public function error_default_branch_read_purchase_return_history()
  {
    $this->success_create_purchase_return();
    $this->unsetBranch();

    $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
    $purchaseReturnUpdated = PurchaseReturn::orderBy('id', 'desc')->first();

    $data = [
        'sort_by' => '-user_activities.date',
        'includes' => 'user',
        'filter_like' => '{}',
        'or_filter_where_has_like[]' => '{"user":{}}',
        'limit' => 10,
        'page' => 1
    ];

    $response = $this->json('GET', self::$path . '/' . $purchaseReturnUpdated->id . '/histories', $data, $this->headers);
    
    $response->assertStatus(422)
      ->assertJson([
          "code" => 422,
          "message" => "please set default branch to read this form"
      ]);
    $this->setBranch();
  }
}