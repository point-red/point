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

class PaymentCollectionReferenceTest extends TestCase
{
    use PaymentCollectionSetup;

    public static $path = '/api/v1/sales/payment-collection';

    /** @test */
    public function success_get_reference()
    {
      $data = $this->getDummy();
        
      $customer  = Customer::orderBy('id', 'desc')->first();
      
      $response = $this->json('GET', self::$path.'/'.$customer->id.'/references', [], $this->headers);
      
      $response->assertStatus(200);
    }

}