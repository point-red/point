<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;
use App\Model\Token;
use App\Model\Form;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Helpers\Inventory\InventoryHelper;

class SalesReturnApprovalTest extends TestCase
{
  use SalesReturnSetup;

  public static $path = '/api/v1/sales/return';
  public static $paycolPath = '/api/v1/sales/payment-collection';

  private $previousSalesReturnData;

  public function create_sales_return($isFirstCreate = true)
  {
      $data = $this->getDummyData();
      
      if($isFirstCreate) {
          $this->setRole();
          $this->previousSalesReturnData = $data;
      }

      $this->json('POST', self::$path, $data, $this->headers);
  }

  /** @test */
  public function error_already_approved_approve_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $salesReturn->form->approval_status = 1;
    $salesReturn->form->save();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);

    $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'form already approved'
      ]);
  }
  
  /** @test */
  public function unauthorized_approve_sales_return()
  {
    $this->create_sales_return();

    $this->unsetUserRole();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);

    $response->assertStatus(500)
      ->assertJson([
        'code' => 0,
        'message' => 'There is no permission named `approve sales return` for guard `api`.'
      ]);
  }
  
  /** @test */
  public function success_approve_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $salesReturnItem = $salesReturn->items[0];

    $stock = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
      'expiry_date' => $salesReturnItem->item->expiry_date,
      'production_number' => $salesReturnItem->item->production_number,
    ]);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);

    $salesReturn->refresh();
    $response->assertStatus(200)
        ->assertJson([
          'data' => [
            'id' => $salesReturn->id,
            'sales_invoice_id' => $salesReturn->sales_invoice_id,
            'warehouse_id' => $salesReturn->warehouse_id,
            'customer_id' => $salesReturn->customer_id,
            'customer_name' => $salesReturn->customer_name,
            'customer_address' => $salesReturn->customer_address,
            'customer_phone' => $salesReturn->customer_phone,
            'tax' => $salesReturn->tax,
            'amount' => $salesReturn->amount,
            'form' => [
              'id' => $salesReturn->form->id,
              'date' => $response->json('data.form.date'),
              'number' => $salesReturn->form->number,
              'edited_number' => $salesReturn->form->edited_number, 
              'edited_notes' => $salesReturn->form->edited_notes,
              'notes' => $salesReturn->form->notes,
              'created_by' => $salesReturn->form->created_by,
              'updated_by' => $response->json('data.form.updated_by'),
              'done' => $salesReturn->form->done,
              'increment' => $salesReturn->form->increment,
              'increment_group' => $salesReturn->form->increment_group,
              'formable_id' => $salesReturn->form->formable_id,
              'formable_type' => $salesReturn->form->formable_type,
              'request_approval_at' => $response->json('data.form.request_approval_at'),
              'request_approval_to' => $salesReturn->form->request_approval_to,
              'approval_by' => $salesReturn->form->approval_by,
              'approval_at' => $response->json('data.form.approval_at'),
              'approval_reason' => $salesReturn->form->approval_reason,
              'approval_status' => 1,
              'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
              'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
              'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
              'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
              'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
              'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
              'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
              'cancellation_status' => $salesReturn->form->cancellation_status,
              'request_close_to' => $salesReturn->form->request_close_to,
              'request_close_by' => $salesReturn->form->request_close_by,
              'request_close_at' => $response->json('data.form.request_close_at'),
              'request_close_reason' => $salesReturn->form->request_close_reason,
              'close_approval_at' => $response->json('data.form.close_approval_at'),
              'close_approval_by' => $salesReturn->form->close_approval_by,
              'close_status' => $salesReturn->form->close_status,
            ]
          ]
        ]);

    $subTotal = $response->json('data.amount') - $response->json('data.tax');
    $this->assertDatabaseHas('forms', [
        'id' => $response->json('data.form.id'),
        'number' => $response->json('data.form.number'),
        'approval_status' => 1
    ], 'tenant');

    $this->assertDatabaseHas('user_activities', [
        'number' => $response->json('data.form.number'),
        'table_id' => $response->json('data.id'),
        'table_type' => 'SalesReturn',
        'activity' => 'Approved'
    ], 'tenant');

    $this->assertDatabaseHas('journals', [
        'form_id' => $response->json('data.form.id'),
        'chart_of_account_id' => $this->arCoa->id,
        'credit' => $response->json('data.amount').'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseHas('journals', [
        'form_id' => $response->json('data.form.id'),
        'chart_of_account_id' => $this->salesIncomeCoa->id,
        'debit' => $subTotal.'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseHas('journals', [
        'form_id' => $response->json('data.form.id'),
        'chart_of_account_id' => $this->taxCoa->id,
        'debit' => $response->json('data.tax').'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseHas('sales_invoice_references', [
      'referenceable_id' => $response->json('data.id'),
      'referenceable_type' => 'SalesReturn',
      'amount' => $response->json('data.amount').'.00000000000000000000000000000'
    ], 'tenant');

    $stockNew = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
      'expiry_date' => $salesReturnItem->item->expiry_date,
      'production_number' => $salesReturnItem->item->production_number,
    ]);
    $this->assertEquals($stockNew, ($stock + $salesReturnItem->quantity));

    $referenced = $this->json('GET', self::$paycolPath. '/'.$salesReturn->customer_id.'/references', [], $this->headers);
    $referenced->assertStatus(200)
      ->assertJson([
          'data' => [
            'salesReturn' => [
              [ 'number' => $salesReturn->form->number ]
            ]
          ]          
      ]);
  }
  
  /** @test */
  public function error_reason_more_than_255_character_reject_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(500);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/reject', $data, $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            'code' => 422,
            'message' => 'The given data was invalid.',
            'errors' => [
              'reason' => [
                'The reason may not be greater than 255 characters.'
              ]
            ]
        ]);
  }
  
  /** @test */
  public function error_empty_reason_reject_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/reject', [], $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            'code' => 422,
            'message' => 'The given data was invalid.',
            'errors' => [
              'reason' => [
                'The reason field is required.'
              ]
            ]
        ]);
  }
  
  /** @test */
  public function unauthorized_reject_sales_return()
  {
    $this->create_sales_return();

    $this->unsetUserRole();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/reject', [], $this->headers);

    $response->assertStatus(500)
      ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `approve sales return` for guard `api`.'
      ]);
  }
  
  /** @test */
  public function success_reject_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(200);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/reject', $data, $this->headers);

    $salesReturn->refresh();

    $response->assertStatus(200)
      ->assertJson([
        'data' => [
          'id' => $salesReturn->id,
          'sales_invoice_id' => $salesReturn->sales_invoice_id,
          'warehouse_id' => $salesReturn->warehouse_id,
          'customer_id' => $salesReturn->customer_id,
          'customer_name' => $salesReturn->customer_name,
          'customer_address' => $salesReturn->customer_address,
          'customer_phone' => $salesReturn->customer_phone,
          'tax' => $salesReturn->tax,
          'amount' => $salesReturn->amount,
          'form' => [
            'id' => $salesReturn->form->id,
            'date' => $response->json('data.form.date'),
            'number' => $salesReturn->form->number,
            'edited_number' => $salesReturn->form->edited_number, 
            'edited_notes' => $salesReturn->form->edited_notes,
            'notes' => $salesReturn->form->notes,
            'created_by' => $salesReturn->form->created_by,
            'updated_by' => $response->json('data.form.updated_by'),
            'done' => $salesReturn->form->done,
            'increment' => $salesReturn->form->increment,
            'increment_group' => $salesReturn->form->increment_group,
            'formable_id' => $salesReturn->form->formable_id,
            'formable_type' => $salesReturn->form->formable_type,
            'request_approval_at' => $response->json('data.form.request_approval_at'),
            'request_approval_to' => $salesReturn->form->request_approval_to,
            'approval_by' => $salesReturn->form->approval_by,
            'approval_at' => $response->json('data.form.approval_at'),
            'approval_reason' => $salesReturn->form->approval_reason,
            'approval_status' => -1,
            'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
            'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
            'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
            'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
            'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
            'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
            'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
            'cancellation_status' => $salesReturn->form->cancellation_status,
            'request_close_to' => $salesReturn->form->request_close_to,
            'request_close_by' => $salesReturn->form->request_close_by,
            'request_close_at' => $response->json('data.form.request_close_at'),
            'request_close_reason' => $salesReturn->form->request_close_reason,
            'close_approval_at' => $response->json('data.form.close_approval_at'),
            'close_approval_by' => $salesReturn->form->close_approval_by,
            'close_status' => $salesReturn->form->close_status,
          ]
        ]
      ]);

    $this->assertDatabaseHas('forms', [
        'id' => $response->json('data.form.id'),
        'number' => $response->json('data.form.number'),
        'approval_status' => -1,
        'done' => 0,
    ], 'tenant');

    $this->assertDatabaseHas('user_activities', [
        'number' => $response->json('data.form.number'),
        'table_id' => $response->json('data.id'),
        'table_type' => 'SalesReturn',
        'activity' => 'Rejected'
    ], 'tenant');

    $subTotal = $response->json('data.amount') - $response->json('data.tax');
    $this->assertDatabaseMissing('journals', [
      'form_id' => $response->json('data.form.id'),
      'chart_of_account_id' => $this->arCoa->id,
      'credit' => $response->json('data.amount').'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseMissing('journals', [
      'form_id' => $response->json('data.form.id'),
      'chart_of_account_id' => $this->salesIncomeCoa->id,
      'debit' => $subTotal.'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseMissing('journals', [
      'form_id' => $response->json('data.form.id'),
      'chart_of_account_id' => $this->taxCoa->id,
      'debit' => $response->json('data.tax').'.000000000000000000000000000000'
    ], 'tenant');
    $this->assertDatabaseMissing('user_activities', [
      'number' => $response->json('data.form.number'),
      'table_id' => $response->json('data.id'),
      'table_type' => 'SalesReturn',
      'activity' => 'Cancel Approved'
  ], 'tenant');
  }
  
  /** @test */
  public function error_no_branch_send_approval_sales_return()
  {
    $this->create_sales_return();

    $this->branchDefault->pivot->is_default = false;
    $this->branchDefault->pivot->save();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['ids'][] = ['id' => $salesReturn->id];

    $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'please set default branch to create this form'
      ]);
  }

  /** @test */
  public function unauthorized_send_approval_sales_return()
  {
    $this->create_sales_return();

    $this->unsetUserRole();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['ids'][] = ['id' => $salesReturn->id];

    $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    $response->assertStatus(500)
      ->assertJson([
        'code' => 0,
        'message' => 'There is no permission named `create sales return` for guard `api`.'
      ]);
  }

  /** @test */
  public function success_send_approval_sales_return()
  {
    $this->create_sales_return();

    $approverToken = Token::orderBy('id', 'asc')->delete();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['ids'][] = ['id' => $salesReturn->id];

    $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    $response->assertStatus(200)
        ->assertJson([
            "input" => [
                "ids" => [
                    [ "id" => $salesReturn->id ]
                ]
            ]
        ]);
  }

  /** @test */
  public function success_send_multiple_approval_sales_return()
  {
    $this->create_sales_return();

    $this->create_sales_return($isFirstCreate = false);
    $salesReturn = SalesReturn::orderBy('id', 'desc')->first();
    $salesReturn->form->cancellation_status = 0;
    $salesReturn->form->close_status = null;
    $salesReturn->form->save();

    $data['ids'] = SalesReturn::get()
        ->pluck('id')
        ->map(function ($id) { return ['id' => $id]; })
        ->toArray();

    $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    $response->assertStatus(200)
        ->assertJson([
            "input" => [
                "ids" => $data['ids']
            ]
        ]);
  }

  /** @test */
  public function success_read_approval_sales_return()
  {
      $this->create_sales_return();

      $data = [
          'join' => 'form,customer,items,item',
          'fields' => 'sales_return.*',
          'sort_by' => '-form.number',
          'group_by' => 'form.id',
          'filter_form'=>'notArchived;null',
          'filter_like'=>'{}',
          'filter_date_min'=>'{"form.date":"2022-05-01 00:00:00"}',
          'filter_date_max'=>'{"form.date":"2022-05-17 23:59:59"}',
          'includes'=>'form;customer;warehouse;items.item;items.allocation',
          'limit'=>10,
          'page' => 1
      ];

      $response = $this->json('GET', self::$path . '/approval', $data, $this->headers);
      
      $response->assertStatus(200)
        ->assertJsonStructure([
          'data' => [
            [
              'id',
              'sales_invoice_id',
              'customer_id',
              'warehouse_id',
              'customer_name',
              'customer_address',
              'customer_phone',
              'tax',
              'amount',
              'form' => [
                'id',
                'date',
                'number',
                'edited_number',
                'edited_notes',
                'notes',
                'created_by',
                'updated_by',
                'done',
                'increment',
                'increment_group',
                'formable_id',
                'formable_type',
                'request_approval_at',
                'request_approval_to',
                'approval_by',
                'approval_at',
                'approval_reason',
                'approval_status',
                'request_cancellation_to',
                'request_cancellation_by',
                'request_cancellation_at',
                'request_cancellation_reason',
                'cancellation_approval_at',
                'cancellation_approval_by',
                'cancellation_approval_reason',
                'cancellation_status',
                'request_close_to',
                'request_close_by',
                'request_close_at',
                'request_close_reason',
                'close_approval_at',
                'close_approval_by',
                'close_status'
              ],
              'customer' => [
                'id',
                'code',
                'tax_identification_number',
                'name',
                'address',
                'city',
                'state',
                'country',
                'zip_code',
                'latitude',
                'longitude',
                'phone',
                'phone_cc',
                'email',
                'notes',
                'credit_limit',
                'branch_id',
                'created_by',
                'updated_by',
                'archived_by',
                'pricing_group_id',
                'label'
              ],
              'items' => [
                  [
                    'id',
                    'sales_return_id',
                    'sales_invoice_item_id',
                    'item_id',
                    'item_name',
                    'quantity',
                    'quantity_sales',
                    'price',
                    'discount_percent',
                    'discount_value',
                    'unit',
                    'converter',
                    'expiry_date',
                    'production_number',
                    'notes',
                    'allocation_id',
                    'allocation',
                  ]
              ]
            ]                
          ],
          'links' => [
            'first',
            'last',
            'prev',
            'next',
          ],
          'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total',
          ]
      ]);
  }
}