<?php 

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use Tests\TestCase;

use App\Model\SettingJournal;
use App\Model\Form;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;

class PurchaseReturnTest extends TestCase
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

    $this->assertDatabaseHas('purchase_returns', [
      'id' => $response->json('data.id'),
      'supplier_name' => $response->json('data.supplier_name'),
      'purchase_invoice_id' => $data->purchase_invoice_id,
    ], 'tenant');

    $this->assertDatabaseHas('purchase_return_items', [
      'purchase_return_id' => $response->json('data.id'),
      'item_id' => $data->items[0]->item_id,
    ], 'tenant');
  }

  /** @test */
  public function unauthorized_create_purchase_return()
  {
    $this->unsetUserRole();
    $data = $this->getDummyData();

    $response = $this->json('POST', self::$path, $data, $this->headers);

    $response->assertStatus(500)
        ->assertJson([
            "code" => 0,
            "message" => "There is no permission named `create purchase return` for guard `api`."
        ]);
  }

  /** @test */
  public function error_default_branch_create_purchase_return()
  {
    $this->setRole();
    
    $data = $this->getDummyData();

    $this->unsetBranch();

    $response = $this->json('POST', self::$path, $data, $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "please set default branch to create this form"
        ]);
    $this->setBranch();
  }

  /** @test */
  public function error_invalid_data_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'purchase_invoice_id', null);

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
  }

  /** @test */
  public function error_purchase_invoice_done_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $purchaseInvoice = PurchaseReturn::findOrFail($data->purchase_invoice_id);
      $purchaseInvoice->form->done = 1;
      $purchaseInvoice->form->save();

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Invoice already done!"
            ]);
  }

  /** @test */
  public function error_note_more_than_255_character_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'notes', $this->faker->text(300));

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Notes can\t more than 255 character!"
          ]);
  }

  /** @test */
  public function error_note_contain_space_on_first_or_last_character_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'notes', ' notes ');

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Notes can\t contain space on first or last character!"
          ]);
  }

  /** @test */
  public function error_overquantity_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'items.0.quantity', 300);

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Purchase return item qty can't exceed purchase invoice qty"
          ]);
  }

  /** @test */
  public function error_invalid_total_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      $data = data_set($data, 'amount', 180000);

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Amount was invalid."
            ]);
  }

  /** @test */
  public function error_journal_not_set_create_purchase_return()
  {
      $this->setRole();

      $data = $this->getDummyData();
      SettingJournal::where('feature', 'purchase')->where('name', 'account payable')->delete();

      $response = $this->json('POST', self::$path, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Journal purchase account - account payable not found"
          ]);
      
      $this->generateChartOfAccount();
  }

  /** @test */
  public function success_read_purchase_return()
  {
      $this->success_create_purchase_return();
      
      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $response = $this->json('GET', self::$path.'/'.$purchaseReturn->id, [
          'includes' => 'items.item;items.allocation;;form.createdBy;form.requestApprovalTo;form.branch'
      ], $this->headers);
      
      $response->assertStatus(200)
          ->assertJson([
              "data" => [
                  "id" => $purchaseReturn->id,
                  "form" => [
                      "id" => $purchaseReturn->form->id,
                      "date" => $purchaseReturn->form->date,
                      "number" => $purchaseReturn->form->number,
                      "notes" => $purchaseReturn->form->notes,
                  ]
              ]
          ]);
  }

  /** @test */
  public function unauthorized_read_purchase_return()
  {   
      $this->success_create_purchase_return();
      $this->unsetUserRole();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $response = $this->json('GET', self::$path.'/'.$purchaseReturn->id, [
          'includes' => 'items.item;items.allocation;;form.createdBy;form.requestApprovalTo;form.branch'
      ], $this->headers);
      
      $response->assertStatus(500)
      ->assertJson([
          "code" => 0,
          "message" => "There is no permission named `read purchase return` for guard `api`."
      ]);
  }

  /** @test */
  public function error_default_branch_read_purchase_return()
  {
    $this->success_create_purchase_return();
    $this->unsetBranch();

    $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

    $response = $this->json('GET', self::$path.'/'.$purchaseReturn->id, [
        'includes' => 'items.item;items.allocation;;form.createdBy;form.requestApprovalTo;form.branch'
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "please set default branch to read this form"
        ]);
    $this->setBranch();
  }

  /** @test */
  public function success_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($purchaseReturn);
      $data = data_set($data, 'items.0.quantity', 10);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(201);
      $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
      $this->assertDatabaseHas('user_activities', [
          'number' => $response->json('data.form.number'),
          'table_id' => $response->json('data.id'),
          'table_type' => 'PurchaseReturn',
          'activity' => 'Update - 1'
      ], 'tenant');

      $this->assertDatabaseHas('forms', [
        'id' => $response->json('data.form.id'),
        'number' => $response->json('data.form.number'),
        'approval_status' => 0,
        'done' => 0,
    ], 'tenant');

      $this->assertDatabaseHas('purchase_returns', [
        'id' => $response->json('data.id'),
        'supplier_name' => $response->json('data.supplier_name'),
        'purchase_invoice_id' => $data->purchase_invoice_id,
      ], 'tenant');
  
      $this->assertDatabaseHas('purchase_return_items', [
        'purchase_return_id' => $response->json('data.id'),
        'item_id' => $data->items[0]->item_id,
      ], 'tenant');
  }

  /** @test */
  public function error_invalid_data_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      $data = data_set($data, 'purchase_invoice_id', null);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
  }

  /** @test */
  public function error_form_already_done_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $purchaseReturn->form->done = 1;
      $purchaseReturn->form->save();

      $data = $this->getDummyData();
      $data = data_set($data, 'purchase_invoice_id', null);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Can't update, form already done!"
            ]);
  }

  /** @test */
  public function error_note_more_than_255_character_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      $data = data_set($data, 'notes', $this->faker->text(300));

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Notes can\t more than 255 character!"
          ]);
  }

  /** @test */
  public function error_note_contain_space_on_first_or_last_character_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      $data = data_set($data, 'notes', ' notes ');

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Notes can\t contain space on first or last character!"
          ]);
  }

  /** @test */
  public function error_overquantity_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      $data = data_set($data, 'items.0.quantity', 300);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Purchase return item qty can't exceed purchase invoice qty"
          ]);
  }

  /** @test */
  public function error_invalid_total_amount_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      $data = data_set($data, 'amount', 18000);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Amount was invalid."
            ]);
  }

  /** @test */
  public function error_journal_not_set_update_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

      $data = $this->getDummyData();
      SettingJournal::where('feature', 'purchase')->where('name', 'account payable')->delete();

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Journal purchase account - account payable not found"
          ]);
      
      $this->generateChartOfAccount();
  }

  /** @test */
  public function unauthorized_edit_purchase_return()
  {   
      $this->success_create_purchase_return();
      $this->unsetUserRole();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($purchaseReturn);
      $data = data_set($data, 'items.0.quantity', 10);

      $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);
      
      $response->assertStatus(500)
      ->assertJson([
          "code" => 0,
          "message" => "There is no permission named `update purchase return` for guard `api`."
      ]);
  }

  /** @test */
  public function error_default_branch_update_purchase_return()
  {
    $this->success_create_purchase_return();
    $this->unsetBranch();

    $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
    
    $data = $this->getDummyData($purchaseReturn);
    $data = data_set($data, 'items.0.quantity', 10);

    $response = $this->json('PATCH', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "please set default branch to update this form"
        ]);
    $this->setBranch();
  }

  /** @test */
  public function success_delete_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(204);
      $this->assertDatabaseHas('forms', [
          'number' => $purchaseReturn->form->number,
          'request_cancellation_reason' => $data['reason'],
          'cancellation_status' => 0,
      ], 'tenant');
  }

  /** @test */
  public function error_form_already_done_delete_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $purchaseReturn->form->done = 1;
      $purchaseReturn->form->save();

      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Can't delete, form already done!"
            ]);
  }

  /** @test */
  public function error_no_reason_delete_purchase_return()
  {
      $this->success_create_purchase_return();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $purchaseReturn->form->done = 1;
      $purchaseReturn->form->save();

      $data['reason'] = null;

      $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

      $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
  }

  /** @test */
  public function unauthorized_delete_purchase_return()
  {   
      $this->success_create_purchase_return();
      $this->unsetUserRole();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);

      $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);
      
      $response->assertStatus(500)
      ->assertJson([
          "code" => 0,
          "message" => "There is no permission named `delete purchase return` for guard `api`."
      ]);
  }

  /** @test */
  public function error_default_branch_delete_purchase_return()
  {
    $this->success_create_purchase_return();
    $this->unsetBranch();

    $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
    $data['reason'] = $this->faker->text(200);

    $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

    $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "please set default branch to delete this form"
        ]);
    $this->setBranch();
  }

}