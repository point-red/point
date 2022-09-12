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

class PaymentCollectionHistoryTest extends TestCase
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
    public function success_create_history()
    {
      $this->success_create_payment_collection();
        
      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

      $dataHistories = [
          "id" => $paymentCollection->id,
          "activity" => 'Update'
      ];

      $response = $this->json('POST', self::$path.'/histories', $dataHistories, [$this->headers]);
      
      $response
        ->assertStatus(201)
        ->assertJsonStructure([ 'data' => ['activity'] ]);
    }

    /** @test */
    public function success_create_histories()
    {
      $this->success_create_payment_collection();
        
      $paymentCollections = PaymentCollection::with('form')->take(2)->get();

      $ids = [];
      foreach ($paymentCollections as $paymentCollection) {
          array_push($ids, $paymentCollection->id);
      }

      $data = [
          "ids" => $ids,
          "activity" => 'Created'
      ];

      $response = $this->json('POST', self::$path.'/histories', $data, [$this->headers]);
      
      $response
        ->assertStatus(201)
        ->assertJsonStructure([ 'data' => ['activity'] ]);
    }

    /** @test */
    public function success_get_history()
    {
      $this->success_create_histories();
        
      $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

      $response = $this->json('GET', self::$path.'/'.$paymentCollection->id.'/histories', [
        'page' => '1',
        'includes' => 'user',
        'limit' => '10',
        'sort_by' => '-user_activity.date',
      ], $this->headers);
      
      $response
        ->assertStatus(200)
        ->assertJsonStructure([ 'data' => [] ]);
    }
}