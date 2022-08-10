<?php

namespace Tests\Feature\Http\Sales\PaymentCollectionV2;

use Tests\Feature\Http\Sales\PaymentCollection\PaymentCollectionSetup;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Form;
use Tests\TestCase;

class PaymentCollectionTest extends TestCase
{
    use PaymentCollectionSetup;

    public static $path = '/api/v1/sales/payment-collection';

    private function createReference($paymentCollection)
    {
        $coa  = ChartOfAccount::orderBy('id', 'asc')->first();
        $payment = new Payment;
        $payment->payment_type = $paymentCollection->payment_type;
        $payment->payment_account_id = $coa->id;
        $payment->disbursed = 0;
        $payment->paymentable_id = $paymentCollection->id;
        $payment->paymentable_id = $paymentCollection->customer_id;
        $payment->paymentable_type = 'Customer';
        $payment->paymentable_name = $paymentCollection->customer_name;
        $payment->save();

        $coa  = ChartOfAccount::orderBy('id', 'desc')->first();
        $paymentDetail = new PaymentDetail;
        $paymentDetail->payment_id = $payment->id;
        $paymentDetail->chart_of_account_id = $coa->id;
        $paymentDetail->amount = $paymentCollection->amount;
        $paymentDetail->referenceable_id = $paymentCollection->id;
        $paymentDetail->referenceable_type = 'PaymentCollection';
        $paymentDetail->save();

        $defaultNumberPostfix = '{y}{m}{increment=4}';
        $form = new Form;
        $form->branch_id =  $paymentCollection->form->branch_id;
        $form->date = date("Y-m-d H:i:s");
        $form->increment_group = date("Ym");
        $form->notes = "some notes";
        $form->created_by = $paymentCollection->form->created_by;
        $form->updated_by = $paymentCollection->form->updated_by;
        $form->formable_id = $payment->id;
        $form->formable_type = 'Payment';
	    $form->number = 'CASH/IN/22080001';
        $form->request_approval_to = $paymentCollection->form->request_approval_to;
        $form->approval_by = $paymentCollection->form->request_approval_to;
        $form->done = 1;
        $form->save();
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
    public function invalid_create_payment_collection()
    {
        $data = $this->getDummy();
        $data = data_set($data, 'payment_type', null);
        
        $response = $this->json('POST', self::$path, $data, $this->headers);
        
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function invalid_reference_create_payment_collection()
    {
        $data = $this->getDummyInvalid();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'Model not found.']);
    }

    /** @test */
    public function invalid_customer_create_payment_collection()
    {
        $data = $this->getDummyInvalidCustomer();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);
        
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Duplicate data entry']);
    }

    /** @test */
    public function read_all_payment_collection()
    {
        $data = [
            'join' => 'form,details,customer',
            'fields' => 'sales_payment_collection.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
        ];

        $response = $this->json('GET', self::$path, $data, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function read_payment_collection()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::orderBy('id', 'desc')->first();

        $data = [
            'with_archives' => 'true',
            'with_origin' => 'true',
            'remaining_info' => 'true',
            'includes' => 'customer;details.referenceable;details.allocation;details.chartOfAccount;form.createdBy;form.requestApprovalTo;form.branch'
        ];

        $response = $this->json('GET', self::$path . '/' . $paymentCollection->id, $data, $this->headers);

        $response->assertStatus(200);
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
            ->assertStatus(201)
            ->assertJsonFragment(['payment_type' => $paymentCollection->payment_type])
            ->assertJsonFragment(['amount' => $paymentCollection->amount])
            ->assertJsonFragment(['customer_id' => $paymentCollection->customer_id])
            ->assertJsonPath('data.form.number', $paymentCollection->form->number);
    }

    /** @test */
    public function failed_update_payment_collection_referenced()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->createReference($paymentCollection);

        $data = $this->getDummy();
        $data = data_set($data, 'id', $paymentCollection->id, false);

        $response = $this->json('PATCH', self::$path.'/'.$paymentCollection->id, $data, [$this->headers]);
        
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot edit form because referenced by payments']);
    }

    /** @test */
    public function failed_update_payment_collection_invalid_branch()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        
        $branch = $this->createBranch();
        $form = $paymentCollection->form;
        $form->branch_id = $branch->id;
        $form->save();

        $data = $this->getDummy();
        $data = data_set($data, 'id', $paymentCollection->id, false);

        $response = $this->json('PATCH', self::$path.'/'.$paymentCollection->id, $data, [$this->headers]);
        
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'please set default branch to save this form']);
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
    public function failed_delete_payment_collection_invalid_branch()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

        $branch = $this->createBranch();
        $form = $paymentCollection->form;
        $form->branch_id = $branch->id;
        $form->save();
        
        $response = $this->json('DELETE', self::$path.'/'.$paymentCollection->id, [], [$this->headers]);
        
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'please set default branch to save this form']);
    }

    /** @test */
    public function failed_delete_payment_collection_referenced()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();
        $this->createReference($paymentCollection);

        $response = $this->json('DELETE', self::$path.'/'.$paymentCollection->id, [], [$this->headers]);
        
        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot edit form because referenced by payments']);
    }

    /** @test */
    public function failed_export_payment_collection()
    {
        $headers = $this->headers;
        unset($headers['Tenant']);

        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

        $data = [
            "data" => [
                "ids" => [$paymentCollection->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days"))
            ]
        ];

        $response = $this->json('POST', self::$path . '/export', $data, $headers);
        $response->assertStatus(500);
    }

    /** @test */
    public function success_export_payment_collection()
    {
        $this->success_create_payment_collection();

        $paymentCollection = PaymentCollection::with('form')->orderBy('id', 'desc')->first();

        $data = [
            "data" => [
                "ids" => [$paymentCollection->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days")),
                "tenant_name" => "development"
            ]
        ];

        $response = $this->json('POST', self::$path . '/export', $data, $this->headers);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([ 'data' => ['url'] ]);
    }

    /** @test */
    public function success_generate_form_number()
    {
        $data = $this->getDummy();

        $response = $this->json('POST', self::$path.'/generate-number', $data, [$this->headers]);
        
        $response
            ->assertStatus(200)
            ->assertJsonStructure([ 'data' => ['number'] ]);
    }
}
