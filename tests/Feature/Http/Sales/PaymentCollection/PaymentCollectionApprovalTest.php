<?php

namespace Tests\Feature\Http\Sales\PaymentCollection;

use App\Model\Sales\PaymentCollection\PaymentCollection;
use Tests\Feature\Http\Sales\PaymentCollection\PaymentCollectionSetup;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Form;
use Tests\TestCase;

class PaymentCollectionApprovalTest extends TestCase
{

  use PaymentCollectionSetup;

  public static $path = '/api/v1/sales/payment-collection';
  public static $pathApproval = '/api/v1/sales/approval/payment-collection';

  public function addUpdateHistory($id) {
    $dataHistories = [
        "id" => $id,
        "activity" => "update"
    ];

    $this->json('POST', self::$path.'/histories', $dataHistories, [$this->headers]);
}

  /** @test */
  public function success_create_payment_collection()
  {
      $data = $this->getDummy();
      
      $response = $this->json('POST', self::$path, $data, $this->headers);
      
      $response
          ->assertStatus(201)
          ->assertJsonFragment(['amount' => 620000]);
  }

  /** @test */
  public function success_delete_payment_collection()
  {
      $this->success_create_payment_collection();

      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
      
      $response = $this->json('DELETE', self::$path.'/'.$paymentCollection->id, [], [$this->headers]);
      
      $response
          ->assertStatus(204);
  }

  /** @test */
  public function success_update_payment_collection()
  {
      $this->success_create_payment_collection();

      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
      
      $data = $this->getDummy();
      $data = data_set($data, 'id', $paymentCollection->id, false);

      $response = $this->json('PATCH', self::$path.'/'.$paymentCollection->id, $data, [$this->headers]);

      $response
          ->assertStatus(201);
  }

  /** @test */
  public function success_approve_payment_collection_reference_pending()
  {
      $this->success_create_payment_collection();
      
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
          "id" => $paymentCollection->id
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/approve', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.approval_status', 1);

      foreach ($paymentCollection->details as $detail) {
        if ($detail->referenceable_type === SalesInvoice::$morphName) {
          $salesInvoice = SalesInvoice::find($detail->referenceable_id);
          $this->assertEquals($salesInvoice->form->done, 0);          
        }
      }
  }

  /** @test */
  public function success_approve_payment_collection_reference_done()
  {
      $data = $this->getDummyReferenceDone();
        
      $this->json('POST', self::$path, $data, $this->headers);
      
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
          "id" => $paymentCollection->id
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/approve', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.approval_status', 1);

      foreach ($paymentCollection->details as $detail) {
        if ($detail->referenceable_type === SalesInvoice::$morphName) {
          $salesInvoice = SalesInvoice::find($detail->referenceable_id);
          $this->assertEquals($salesInvoice->form->done, 1);          
        }
      }
  }

  /** @test */
  public function failed_approve_payment_collection_not_enough_amount()
  {
      $this->success_approve_payment_collection_reference_done();
        
      $data = $this->getDummyWithoutCreate();

      $this->json('POST', self::$path, $data, $this->headers);
      
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
          "id" => $paymentCollection->id
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/approve', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.approval_status', 0);
  }

  /** @test */
  public function success_reject_payment_collection()
  {
      $this->success_create_payment_collection();
        
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
        "id" => $paymentCollection->id,
        "reason" => "some reason"
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/reject', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.approval_status', -1);
  }

  /** @test */
  public function failed_reject_payment_collection()
  {
      $this->success_create_payment_collection();
        
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
        "id" => $paymentCollection->id
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/reject', $data, $this->headers);
      
      $response
        ->assertStatus(500);
  }

  /** @test */
  public function success_send_approval_payment_collection()
  {
      $this->success_create_payment_collection();
        
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
        "id" => $paymentCollection->id,
        "form_send_done" => 1,
        "crud_type" => "create"
      ];

      $response = $this->json('POST', self::$pathApproval.'/'.$paymentCollection->id.'/send', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'input' => ['authUser'] ]);
  }

  /** @test */
  public function success_send_update_approval_payment_collection()
  {
      $this->success_update_payment_collection();
        
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
        "id" => $paymentCollection->id,
        "form_send_done" => 1,
        "crud_type" => "update"
      ];

      $response = $this->json('POST', self::$pathApproval.'/'.$paymentCollection->id.'/send', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'input' => ['authUser'] ]);
  }

  /** @test */
  public function success_send_cancelation_approval_payment_collection()
  {
      $this->success_delete_payment_collection();
        
      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();
        
      $data = [
        "id" => $paymentCollection->id,
        "form_send_done" => 1,
        "crud_type" => "delete"
      ];

      $response = $this->json('POST', self::$pathApproval.'/cancellation/'.$paymentCollection->id.'/send', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'input' => ['authUser'] ]);
  }

  /** @test */
  public function success_read_approval_list_payment_collection()
  {
      $this->success_create_payment_collection();

      $data = [
        'limit' => 10,
        'page' => 1
      ];

      $response = $this->json('GET', self::$pathApproval , $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'data' => [] ]);
  }

  /** @test */
  public function success_send_approval_single()
  {
      $this->success_create_payment_collection();

      $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();

      $ids = [];
      array_push($ids, $paymentCollection->id);

      $data = [
          "ids" => $ids
      ];

      $response = $this->json('POST', self::$pathApproval.'/send', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'input' => ['ids'] ]);
  }

  /** @test */
  public function success_send_approval_all()
  {
      $this->success_create_payment_collection();
      $data = $this->getDummyWithoutCreate();

      $this->json('POST', self::$path, $data, $this->headers);
      $this->json('POST', self::$path, $data, $this->headers);
      $this->json('POST', self::$path, $data, $this->headers);

      $paymentCollections = PaymentCollection::orderBy('id', 'desc')->take(4)->get();

      $ids = [];
      $idx = 0;
      foreach ($paymentCollections as $paymentCollection) {
          if ($idx === 1) {
              $updateData = $this->getDummy();

              $updateData["id"] = $paymentCollection->id;

              $responseUpdate = $this->json('PATCH', self::$path.'/'.$paymentCollection->id, $updateData, [$this->headers]);

              $dataHistories = [
                  "id" => $paymentCollection->id,
                  "activity" => "update"
              ];

              $this->json('POST', self::$path.'/histories', $dataHistories, [$this->headers]);

          }
          if ($idx === 2) {
              $responseDelete = $this->json('DELETE', self::$path.'/'.$paymentCollection->id, [], [$this->headers]);
          }
          $id = [ "id" => $paymentCollection->id];
          array_push($ids, $id);
          $idx++;
      }

      $data = [
          "ids" => $ids
      ];

      $response = $this->json('POST', self::$pathApproval.'/send', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'input' => ['ids'] ]);
  }

}