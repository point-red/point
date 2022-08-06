<?php

namespace Tests\Feature\Http\Sales\PaymentCollection;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use Tests\Feature\Http\Sales\PaymentCollection\PaymentCollectionSetup;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Customer;
use App\Model\Form;
use App\Model\Token;
use Tests\TestCase;

class PaymentCollectionApprovalByEmailTest extends TestCase
{
    use PaymentCollectionSetup;

    public static $path = '/api/v1/sales/payment-collection';
    public static $pathApproval = '/api/v1/sales/payment-collection/approve';

    private function findOrCreateToken($tenantUser)
    {
        $approverToken = Token::where('user_id', $tenantUser->id)->first();
        if (!$approverToken) {
            $approverToken = new Token();
            $approverToken->user_id = $tenantUser->id;
            $approverToken->token = md5($tenantUser->email.''.now());
            $approverToken->save();
        }

        return $approverToken;
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
    public function success_approve_payment_collection_reference_done()
    {
        $data = $this->getDummyReferenceDone();
          
        $this->json('POST', self::$path, $data, $this->headers);
        
        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/approve', $data, $this->headers);
        
        $response
          ->assertStatus(200);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->approval_status, 1);

        foreach ($paymentCollection->details as $detail) {
          if ($detail->referenceable_type === SalesInvoice::$morphName) {
            $salesInvoice = SalesInvoice::find($detail->referenceable_id);
            $this->assertEquals($salesInvoice->form->done, 1);
          }
        }
    }

    /** @test */
    public function success_approve_payment_collection_reference_peding()
    {
        $data = $this->success_create_payment_collection();
        
        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/approve', $data, $this->headers);
        
        $response
          ->assertStatus(200);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->approval_status, 1);

        foreach ($paymentCollection->details as $detail) {
          if ($detail->referenceable_type === SalesInvoice::$morphName) {
            $salesInvoice = SalesInvoice::find($detail->referenceable_id);
            $this->assertEquals($salesInvoice->form->done, 0);
          }
        }
    }

    /** @test */
    public function success_approve_cancellation_payment_collection()
    {
        $this->success_delete_payment_collection();
          
        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/approve', $data, $this->headers);
        
        $response
          ->assertStatus(200);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->cancellation_status, 1);

        foreach ($paymentCollection->details as $detail) {
          if ($detail->referenceable_type === SalesInvoice::$morphName) {
            $salesInvoice = SalesInvoice::find($detail->referenceable_id);
            $this->assertEquals($salesInvoice->form->done, 0);
          }
        }
    }

    /** @test */
    public function failed_approve_payment_collection_reference_not_enough_amount()
    {
        $this->success_approve_payment_collection_reference_done();
          
        $data = $this->getDummyWithoutCreate();

        $this->json('POST', self::$path, $data, $this->headers);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/approve', $data, $this->headers);
        
        $response
          ->assertStatus(200);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->approval_status, 0);
    }

    /** @test */
    public function failed_approve_payment_collection()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => null,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/approve', $data, $this->headers);
        
        $response
          ->assertStatus(422)
          ->assertJsonFragment(['message' => 'Approve email failed']);
    }

    /** @test */
    public function failed_reject_payment_collection()
    {
        //failed
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = TenantUser::findOrFail($paymentCollection->form->request_approval_to);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => null,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/reject', $data, $this->headers);
        
        $response
          ->assertStatus(422)
          ->assertJsonFragment(['message' => 'Approve email failed']);
    }

    /** @test */
    public function success_reject_payment_collection()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = $paymentCollection->form->requestApprovalTo;
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/reject', $data, $this->headers);
        $response
          ->assertStatus(200);

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->approval_status, -1);
    }

    /** @test */
    public function success_reject_cancellation_payment_collection()
    {
        $this->success_delete_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
          
        $approver = $paymentCollection->form->requestApprovalTo;
        $token = $this->findOrCreateToken($approver);

        $data = [
            "ids" => [$paymentCollection->id],
            "token" => $token->token,
            "approver_id" => $approver->id
        ];

        $response = $this->json('POST', self::$path.'/reject', $data, $this->headers);
        $response
          ->assertStatus(200);
          
        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->assertEquals($paymentCollection->form->cancellation_status, -1);
    }

}