<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Helpers\Inventory\InventoryHelper;

class SalesReturnCancellationApprovalTest extends TestCase
{
  use SalesReturnSetup;

  public static $path = '/api/v1/sales/return';
  
  public function create_sales_return($isFirstCreate = true)
  {
    $data = $this->getDummyData();
    
    if($isFirstCreate) {
        $this->setRole();
        $this->previousSalesReturnData = $data;
    }

    $this->json('POST', self::$path, $data, $this->headers);
  }

  public function approve_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);
  }

  public function delete_sales_return()
  {
    $this->create_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(200);

    $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
 }

 public function delete_approved_sales_return()
  {
    $this->approve_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(200);

    $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
 }

  /** @test */
  public function error_already_cancelled_approve_sales_return()
  {
    $this->delete_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $salesReturn->form->cancellation_status = 1;
    $salesReturn->form->save();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);

    $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'form not in cancellation pending state'
      ]);
  }
  
  /** @test */
  public function unauthorized_approve_approve_cancel_sales_return()
  {
    $this->delete_sales_return();

    $this->unsetUserRole();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);

    $response->assertStatus(500)
      ->assertJson([
        'code' => 0,
        'message' => 'There is no permission named `approve sales return` for guard `api`.'
      ]);
  }
  
  /** @test */
  public function success_approve_cancel_sales_return()
  {
    $this->delete_approved_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $salesReturnItem = $salesReturn->items[0];

    $stock = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
      'expiry_date' => $salesReturnItem->item->expiry_date,
      'production_number' => $salesReturnItem->item->production_number,
    ]);

    $amountInvoice = SalesInvoice::getAvailable($salesReturn->salesInvoice);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);
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
              'approval_status' => $salesReturn->form->approval_status,
              'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
              'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
              'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
              'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
              'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
              'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
              'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
              'cancellation_status' => 1,
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
        'cancellation_status' => 1
    ], 'tenant');

    $this->assertDatabaseHas('user_activities', [
        'number' => $response->json('data.form.number'),
        'table_id' => $response->json('data.id'),
        'table_type' => 'SalesReturn',
        'activity' => 'Cancel Approved'
    ], 'tenant');

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

    $stockNew = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
      'expiry_date' => $salesReturnItem->item->expiry_date,
      'production_number' => $salesReturnItem->item->production_number,
    ]);
    $this->assertEquals($stockNew, $stock - $salesReturnItem->quantity);

    $salesReturn->refresh();
    $amountInvoiceNew = SalesInvoice::getAvailable($salesReturn->salesInvoice);
    $this->assertEquals($amountInvoiceNew, $amountInvoice + $salesReturn->amount);
  }
  
  /** @test */
  public function success_reject_cancel_sales_return()
  {
    $this->delete_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $salesReturnItem = $salesReturn->items[0];

    $data['reason'] = $this->faker->text(200);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', $data, $this->headers);
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
              'approval_status' => $salesReturn->form->approval_status,
              'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
              'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
              'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
              'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
              'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
              'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
              'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
              'cancellation_status' => -1,
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
        'cancellation_status' => -1
    ], 'tenant');

    $this->assertDatabaseHas('user_activities', [
        'number' => $response->json('data.form.number'),
        'table_id' => $response->json('data.id'),
        'table_type' => 'SalesReturn',
        'activity' => 'Cancel Rejected'
    ], 'tenant');
  }

  /** @test */
  public function error_reason_more_than_255_character_reject_cancel_sales_return()
  {
    $this->delete_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(500);

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', $data, $this->headers);

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
  public function error_empty_reason_reject_cancel_sales_return()
  {
    $this->delete_sales_return();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', [], $this->headers);

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
  public function unauthorized_reject_cancel_sales_return()
  {
    $this->delete_sales_return();

    $this->unsetUserRole();

    $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

    $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', [], $this->headers);

    $response->assertStatus(500)
      ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `approve sales return` for guard `api`.'
      ]);
  }
}
