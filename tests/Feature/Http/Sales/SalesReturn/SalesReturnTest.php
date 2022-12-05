<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Sales\SalesReturn\SalesReturn;

class SalesReturnTest extends TestCase
{
  use SalesReturnSetup;

  public static $path = '/api/v1/sales/return';

  /** @test */
  public function unauthorized_create_sales_return()
  {
      $data = $this->getDummyData();

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(500)
          ->assertJson([
              "code" => 0,
              "message" => "There is no permission named `create sales return` for guard `api`."
          ]);
  }

  /** @test */
  public function overquantity_create_sales_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'items.0.quantity', 100);

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Sales return item can't exceed sales invoice qty"
          ]);
  }

  /** @test */
  public function invalid_create_sales_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'sales_invoice_id', null);

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "The given data was invalid."
        ]);
  }
  
  /** @test */
  public function success_create_sales_return()
  {
    $this->setRole();

    $data = $this->getDummyData();

    $response = $this->json('POST', self::$path, $data, $this->headers);

    $response->assertStatus(201)
        ->assertJson([
            "data" => [
                "id" => $response->json('data.id'),
                "form" => [
                    "id" => $response->json('data.form.id'),
                    "date" => $response->json('data.form.date'),
                    "number" => $response->json('data.form.number'),
                    "notes" => $response->json('data.form.notes'),
                ]
            ]
        ]);

    $this->assertDatabaseHas('forms', [
        'id' => $response->json('data.form.id'),
        'number' => $response->json('data.form.number'),
        'approval_status' => 0,
        'done' => 0,
    ], 'tenant');
  }

  /** @test */
  public function success_approve_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

      $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);

      $response->assertStatus(200)
        ->assertJson([
            "data" => [
                "id" => $salesReturn->id,
                "form" => [
                    "id" => $salesReturn->form->id,
                    "date" => $salesReturn->form->date,
                    "number" => $salesReturn->form->number,
                    "notes" => $salesReturn->form->notes,
                ]
            ]
        ]);

      $this->assertDatabaseHas('forms', [
          'id' => $response->json('data.form.id'),
          'number' => $response->json('data.form.number'),
          'approval_by' => $response->json('data.form.approval_by'),
          'approval_status' => 1,
      ], 'tenant');
      $this->assertDatabaseHas('inventories', [
        'form_id' => $response->json('data.form.id'),
        'item_id' => $response->json('data.items.0.item_id'),
        'quantity' => $response->json('data.items.0.quantity'),
    ], 'tenant');
  }

  /** @test */
  public function read_all_sales_return()
  {
      $this->success_create_sales_return();

      $data = [
          'join' => 'form,customer,items,item',
          'fields' => 'sales_return.*',
          'sort_by' => '-form.number',
          'group_by' => 'form.id',
          'filter_form' => 'notArchived;null',
          'filter_like' => '{}',
          'limit' => 10,
          'includes' => 'form;customer;items.item;items.allocation',
          'page' => 1
      ];

      $response = $this->json('GET', self::$path, $data, $this->headers);

      $response->assertStatus(200);
      $this->assertGreaterThan(0, count($response->json('data')));
  }

  /** @test */
  public function read_sales_return()
  {
      $this->success_approve_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

      $data = [
          'with_archives' => 'true',
          'with_origin' => 'true',
          'remaining_info' => 'true',
          'includes' => 'customer;warehouse;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch'
      ];

      $response = $this->json('GET', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(200)
        ->assertJson([
            "data" => [
                "id" => $salesReturn->id,
                "form" => [
                    "id" => $salesReturn->form->id,
                    "date" => $salesReturn->form->date,
                    "number" => $salesReturn->form->number,
                    "notes" => $salesReturn->form->notes,
                ]
            ]
        ]);
  }

  /** @test */
  public function unauthorized_update_sales_return()
  {
      $this->success_create_sales_return();

      $this->unsetUserRole();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data = $this->getDummyData($salesReturn);

      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(500)
          ->assertJson([
              "code" => 0,
              "message" => "There is no permission named `update sales return` for guard `api`."
          ]);
  }

  /** @test */
  public function referenced_update_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

      $this->createPaymentCollection($salesReturn);
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);

      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot edit form because referenced by payment collection']);
  }

  /** @test */
  public function overquantity_update_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'items.0.quantity', 100);

      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Sales return item can't exceed sales invoice qty"
          ]);
  }

  /** @test */
  public function invalid_update_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'sales_invoice_id', null);

      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "The given data was invalid."
          ]);
  }

  /** @test */
  public function success_update_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);

      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(201)
        ->assertJson([
            "data" => [
                "id" => $response->json('data.id'),
                "form" => [
                    "id" => $response->json('data.form.id'),
                    "date" => $response->json('data.form.date'),
                    "number" => $response->json('data.form.number'),
                    "notes" => $response->json('data.form.notes'),
                ]
            ]
        ]);

      $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
      $this->assertDatabaseHas('user_activities', [
          'number' => $response->json('data.form.number'),
          'table_id' => $response->json('data.id'),
          'table_type' => 'SalesReturn',
          'activity' => 'Update - 1'
      ], 'tenant');
  }

  /** @test */
  public function unauthorized_delete_sales_return()
  {
      $this->success_create_sales_return();

      $this->unsetUserRole();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(500)
          ->assertJson([
              "code" => 0,
              "message" => "There is no permission named `delete sales return` for guard `api`."
          ]);
  }

  /** @test */
  public function referenced_delete_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $this->createPaymentCollection($salesReturn);
      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot edit form because referenced by payment collection']);
  }

  /** @test */
  public function success_delete_sales_return()
  {
      $this->success_create_sales_return();

      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);

      $response->assertStatus(204);
      
      $this->assertDatabaseHas('forms', [
          'number' => $salesReturn->form->number,
          'request_cancellation_reason' => $data['reason'],
          'cancellation_status' => 0,
      ], 'tenant');
  }
}