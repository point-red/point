<?php

namespace Tests\Feature\Http\Sales\PaymentCollection;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use Tests\Feature\Http\Sales\PaymentCollection\PaymentCollectionSetup;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Customer;
use App\Model\Form;
use App\Model\Token;
use Tests\TestCase;

class PaymentCollectionCancellationApprovalTest extends TestCase
{
    use PaymentCollectionSetup;

    public static $path = '/api/v1/sales/payment-collection';

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
    public function success_approve_cancellation_payment_collection()
    {
      $this->success_delete_payment_collection();
        
      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/cancellation-approve', [], $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.cancellation_status', 1);
    }

    /** @test */
    public function success_reject_cancellation_payment_collection()
    {
      $this->success_delete_payment_collection();
        
      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

      $data = [
        "id" => $paymentCollection->id,
        "reason" => "some reason"
      ];

      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/cancellation-reject', $data, $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonPath('data.form.cancellation_status', -1);
    }

    /** @test */
    public function failed_reject_cancellation_payment_collection()
    {
      $this->success_delete_payment_collection();
        
      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();


      $response = $this->json('POST', self::$path.'/'.$paymentCollection->id.'/cancellation-reject', [], $this->headers);
      
      $response
        ->assertStatus(500);
    }
}