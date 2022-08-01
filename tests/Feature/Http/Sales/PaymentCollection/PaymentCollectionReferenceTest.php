<?php

namespace Tests\Feature\Http\Sales\PaymentCollection;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Customer;
use App\Model\Form;
use Tests\TestCase;

class PaymentCollectionReferenceTest extends TestCase
{

    public static $path = '/api/v1/sales/payment-collection';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();
        $_SERVER['HTTP_REFERER'] = 'http://www.example.com/';
    }

    public function createDummyUser() {
      $user = new TenantUser;
      $user->name = $this->faker->name;
      $user->address = $this->faker->address;
      $user->phone = $this->faker->phoneNumber;
      $user->email = $this->faker->email;
      $user->save();

      return $user;
  }

  public function dummySalesInvoice($customer)
  {
      $customer = Customer::orderBy('id', 'asc')->first();
      if (!$customer) {
          $customer = factory(Customer::class)->create();
      }        

      $salesInvoice = new SalesInvoice;
      $salesInvoice->customer_id = $customer->id;
      $salesInvoice->customer_name = $customer->name;
      $salesInvoice->amount = 2000000;

      $salesInvoice->save();

      $branch = $this->createBranch();

      $user = $this->createDummyUser();

      $defaultNumberPostfix = '{y}{m}{increment=4}';

      $lastForm = Form::where('formable_type', 'SalesInvoice')
              ->whereNotNull('number')
              ->where('increment_group', date("Ym"))
              ->orderBy('increment', 'desc')
              ->first();

      $form = new Form;
      $form->branch_id =  $branch->id;
      $form->date = date("Y-m-d H:i:s");
      $form->increment_group = date("Ym");
      $form->notes = "some notes";
      $form->created_by = $user->id;
      $form->updated_by = $user->id;
      $form->formable_id = $salesInvoice->id;
      $form->formable_type = 'SalesInvoice';
    $form->generateFormNumber(
        'SI'.$defaultNumberPostfix,
        $salesInvoice->customer_id,
        $salesInvoice->supplier_id
    );
      $form->request_approval_to = $user->id;

      $form->save();        
  }

  public function dummySalesReturn($customer)
  {
      $salesInvoice  = SalesInvoice::orderBy('id', 'asc')->first();

      $salesReturn = new SalesReturn;
      $salesReturn->sales_invoice_id = $salesInvoice->id;
      $salesReturn->customer_id = $customer->id;
      $salesReturn->customer_name = $customer->name;
      $salesReturn->amount = 200000;

      $salesReturn->save();

      $branch = $this->createBranch();

      $user = $this->createDummyUser();

      $defaultNumberPostfix = '{y}{m}{increment=4}';

      $lastForm = Form::where('formable_type', 'SalesReturn')
              ->whereNotNull('number')
              ->where('increment_group', date("Ym"))
              ->orderBy('increment', 'desc')
              ->first();

      $form = new Form;
      $form->branch_id =  $branch->id;
      $form->date = date("Y-m-d H:i:s");
      $form->increment_group = date("Ym");
      $form->notes = "some notes";
      $form->created_by = $user->id;
      $form->updated_by = $user->id;
      $form->formable_id = $salesReturn->id;
      $form->formable_type = 'SalesReturn';
    $form->generateFormNumber(
        'SR'.$defaultNumberPostfix,
        $salesReturn->customer_id,
        $salesReturn->supplier_id
    );
      $form->request_approval_to = $user->id;

      $form->save();   
  }

  public function dummyDownPayment($customer)
  {
      $salesDownPayment = new SalesDownPayment;
      $salesDownPayment->customer_id = $customer->id;
      $salesDownPayment->customer_name = $customer->name;
      $salesDownPayment->amount = 500000;

      $salesDownPayment->save();

      $branch = $this->createBranch();

      $user = $this->createDummyUser();

      $defaultNumberPostfix = '{y}{m}{increment=4}';

      $lastForm = Form::where('formable_type', 'SalesDownPayment')
              ->whereNotNull('number')
              ->where('increment_group', date("Ym"))
              ->orderBy('increment', 'desc')
              ->first();

      $form = new Form;
      $form->branch_id =  $branch->id;
      $form->date = date("Y-m-d H:i:s");
      $form->increment_group = date("Ym");
      $form->notes = "some notes";
      $form->created_by = $user->id;
      $form->updated_by = $user->id;
      $form->formable_id = $salesDownPayment->id;
      $form->formable_type = 'SalesDownPayment';
    $form->generateFormNumber(
        'DP'.$defaultNumberPostfix,
        $salesDownPayment->customer_id,
        $salesDownPayment->supplier_id
    );
      $form->request_approval_to = $user->id;

      $form->save();
  }

    public function dummyData() {

        $customer = factory(Customer::class)->create();
        $this->dummySalesInvoice($customer);
        $this->dummySalesReturn($customer);
        $this->dummyDownPayment($customer);

    }

    public function testGetReferenceList()
    {
      $this->dummyData();

      $customer  = Customer::orderBy('id', 'desc')->first();
      
      $response = $this->json('GET', self::$path.'/'.$customer->id.'/references', [], $this->headers);
      
      $response->assertStatus(200);
    }
        
}
