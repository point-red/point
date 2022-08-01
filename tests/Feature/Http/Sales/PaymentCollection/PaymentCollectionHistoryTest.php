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

class PaymentCollectionHistoryTest extends TestCase
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

    public function dummyChartofAccount()
    {
        $user = TenantUser::orderBy('id', 'asc')->first();
        if (!$user) {
            $user = $this->createDummyUser();
        }

        $chartOfAccountTypeDebit = ChartOfAccountType::where('is_debit', 1)->first();
        
        if (!$chartOfAccountTypeDebit) {
            $chartOfAccountTypeDebit = new ChartOfAccountType;
            $chartOfAccountTypeDebit->name = 'OTHER EXPENSE';
            $chartOfAccountTypeDebit->alias = 'BEBAN NON OPERASIONAL';
            $chartOfAccountTypeDebit->is_debit = 1;

            $chartOfAccountTypeDebit->save();
        }

        $chartOfAccountTypeCredit = ChartOfAccountType::where('is_debit', 0)->first();
        if(!$chartOfAccountTypeCredit) {
            $chartOfAccountTypeCredit = new ChartOfAccountType;
            $chartOfAccountTypeCredit->name = 'OTHER INCOME';
            $chartOfAccountTypeCredit->alias = 'PENDAPATAN LAIN-LAIN';
            $chartOfAccountTypeCredit->is_debit = 0;

            $chartOfAccountTypeCredit->save();
        }        

        $chartOfAccountCredit = ChartOfAccount::where('position', 'CREDIT')->first();
        if(!$chartOfAccountCredit) {
            $chartOfAccountCredit = new ChartOfAccount;
            $chartOfAccountCredit->type_id = $chartOfAccountTypeDebit->id;
            $chartOfAccountCredit->position = 'CREDIT';
            $chartOfAccountCredit->number = '41106';
            $chartOfAccountCredit->name = 'OTHER INCOME';
            $chartOfAccountCredit->alias = 'PENDAPATAN LAIN-LAIN';
            $chartOfAccountCredit->created_by = $user->id;
            $chartOfAccountCredit->updated_by = $user->id;

            $chartOfAccountCredit->save();
        }

        $chartOfAccountDebit = ChartOfAccount::where('position', 'DEBIT')->first();
        if (!$chartOfAccountDebit) {
            $chartOfAccountDebit = new ChartOfAccount;
            $chartOfAccountDebit->type_id = $chartOfAccountTypeCredit->id;
            $chartOfAccountDebit->position = 'DEBIT';
            $chartOfAccountDebit->number = '51107';
            $chartOfAccountDebit->name = 'OFFICE ADMINISTRATION EXPENSE';
            $chartOfAccountDebit->alias = 'ADMINISTRASI BANK';
            $chartOfAccountDebit->created_by = $user->id;
            $chartOfAccountDebit->updated_by = $user->id;

            $chartOfAccountDebit->save();
        }        
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

        $salesInvoice  = SalesInvoice::orderBy('id', 'asc')->first();
        $salesReturn  = SalesReturn::orderBy('id', 'asc')->first();
        $salesDownPayment  = SalesDownPayment::orderBy('id', 'asc')->first();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $customer = Customer::findOrFail($salesInvoice->customer->id);

        $user = new TenantUser;
        $user->name = $this->faker->name;
        $user->address = $this->faker->address;
        $user->phone = $this->faker->phoneNumber;
        $user->email = $this->faker->email;
        $user->save();

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => $customer->id,
            "customer_name" => $customer->name,
            "payment_type" => "cash",
            "request_approval_to" => $user->id,
            "details" => [
                [
                  "date" => date("Y-m-d H:i:s"),
                  "chart_of_account_id" => null,
                  "chart_of_account_name" => null,
                  "available" => $salesInvoice->amount,
                  "amount" => 800000,
                  "allocation_id" => null,
                  "allocation_name" => null,
                  "referenceable_form_date" => $salesInvoice->form->date,
                  "referenceable_form_number" => $salesInvoice->form->number,
                  "referenceable_form_notes" => $salesInvoice->form->notes,
                  "referenceable_id" => $salesInvoice->id,
                  "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesReturn->amount,
                    "amount" => 80000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesReturn->form->date,
                    "referenceable_form_number" => $salesReturn->form->number,
                    "referenceable_form_notes" => $salesReturn->form->notes,
                    "referenceable_id" => $salesReturn->id,
                    "referenceable_type" => "SalesReturn"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesDownPayment->amount,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesDownPayment->form->date,
                    "referenceable_form_number" => $salesDownPayment->form->number,
                    "referenceable_form_notes" => $salesDownPayment->form->notes,
                    "referenceable_id" => $salesDownPayment->id,
                    "referenceable_type" => "SalesDownPayment"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }

    public function testCreate()
    {
        
        $this->dummyChartofAccount();

        $data = $this->dummyData();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
    }
    

    public function testCreateHistory() {
      // test create
      $this->testCreate();

      $salesPaymentCollection = PaymentCollection::orderBy('id', 'desc')->first();

      $dataHistories = [
          "id" => $salesPaymentCollection->id,
          "activity" => 'Update'
      ];

      $response = $this->json('POST', self::$path.'/histories', $dataHistories, [$this->headers]);
      $response->assertStatus(201);

      // test create many
      $this->testCreate();

      $salesPaymentCollections = PaymentCollection::orderBy('id', 'desc')->take(2)->get();

      $ids = [];
      foreach ($salesPaymentCollections as $paymentCollection) {
          array_push($ids, $paymentCollection->id);
      }

      $data = [
          "ids" => $ids,
          "activity" => 'Created'
      ];

      $response = $this->json('POST', self::$path.'/histories', $data, [$this->headers]);
      
      $response->assertStatus(201);
      
    }

    public function testGetHistories()
    {
      $this->testCreate();

      $salesPaymentCollection = PaymentCollection::orderBy('id', 'desc')->first();

      $response = $this->json('GET', self::$path.'/'.$salesPaymentCollection->id.'/histories', [
          'page' => '1',
          'includes' => 'user',
          'limit' => '10',
          'sort_by' => '-user_activity.date',
      ], $this->headers);
      
      $response->assertStatus(200);
    }

}
