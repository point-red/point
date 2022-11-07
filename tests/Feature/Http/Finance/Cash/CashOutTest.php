<?php

namespace Tests\Feature\Http\Finance\Cash;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\Journal;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Allocation;
use App\Model\Master\Customer;
use App\Model\Master\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CashOutTest extends TestCase
{
    public static $path = '/api/v1/finance';

    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->setProject();
        $this->importChartOfAccount();
    }

    private function importChartOfAccount()
    {
        Excel::import(new ChartOfAccountImport(), storage_path('template/chart_of_accounts_manufacture.xlsx'));

        $this->artisan('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);
    }

    public function getChartOfAccountCash()
    {
        return ChartOfAccount::whereHas('type', function ($query) {
            $query->where('name', 'CASH');
        })->first();
    }

    public function createPaymentOrder($onlyGetData = false)
    {
        $countDetails = rand(1, 5);

        $coas = ChartOfAccount::whereHas('type', function ($query) {
            $query->whereIn('name', ['DIRECT EXPENSE', 'OTHER EXPENSE', 'OTHER CURRENT ASSET', 'INCOME TAX RECEIVABLE', 'INCOME TAX PAYABLE', 'OTHER ACCOUNT RECEIVABLE', 'OTHER CURRENT LIABILITY', 'LIABILITAS JANGKA PANJANG', 'FACTORY OVERHEAD COST']);
        })->inRandomOrder()->limit($countDetails)->get();
        $details = [];
        for ($i = 0; $i < $countDetails; $i++) {
            ${'coa' . $i} = $coas->skip($i)->first();
            $details[$i] = [
                'chart_of_account_id' => ${'coa' . $i}->id,
                'chart_of_account_name' => ${'coa' . $i}->label,
                'amount' => rand(1, 100) * 1_000,
                'allocation_id' => factory(Allocation::class)->create()->id
            ];
        }

        $paymentable = factory(Customer::class)->create();
        $form = [
            'payment_type' => 'cash',
            'due_date' => Carbon::create(date('Y'), date('m'), rand(1, 28)),
            'paymentable_id' => $paymentable->id,
            'paymentable_type' => $paymentable::$morphName,
            'details' => $details
        ];

        if ($onlyGetData) {
            return $form;
        }
        return PaymentOrder::create($form);
    }

    public function createCashAdvance($amount)
    {
        // Copied & adjusted from CashAdvanceTest/createDataCashAdvance & CashAdvanceTest/makePaymentCashIn
        $account = ChartOfAccount::where('name', 'CASH')->first();
        $account_detail = ChartOfAccount::where('name', 'OTHER INCOME')->first();

        $employee = factory(Employee::class)->create();
        $user = factory(User::class)->create();

        // s: insert cash in
        $amount_account = rand(10, 100) * 100_000;
        $data = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s'),
            'payment_type' => "cash",
            'payment_account_id' => $account->id,
            'paymentable_id' => $employee->id,
            'paymentable_name' => $employee->name,
            'paymentable_type' => Employee::$morphName,
            'disbursed' => false,
            'notes' => null,
            'amount' => $amount_account,
            'details' => array(
                [
                    'chart_of_account_id' => $account_detail->id,
                    'amount' => $amount_account,
                    'allocation_id' => null,
                    'allocation_name' => null,
                    'notes' => "Kas"
                ]
            )
        ];
        $payment = Payment::create($data);

        $form = $payment->form;
        $journal = new Journal;
        $journal->form_id = $form->id;
        $journal->chart_of_account_id = $account->id;
        $journal->debit = $amount_account;
        $journal->save();

        //create sample cash advance
        $data = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'payment_type' => 'cash',
            'employee_id' => $employee->id,
            'request_approval_to' => $user->id,
            'notes' => 'Notes Form',
            'amount' => $amount,
            'activity' => 'Created',
            'details' => array(
                [
                    'chart_of_account_id' => $account->id,
                    'amount' => $amount,
                    'notes' => 'Notes'
                ]
            )
        ];

        return CashAdvance::create($data);
    }

    public function transformPaymentOrderDetails($paymentOrder)
    {
        return $paymentOrder->details->transform(function ($detail) {
            return [
                'allocation_id' => $detail->allocation_id,
                'amount' => $detail->amount,
                'chart_of_account_id' => $detail->chart_of_account_id,
                'notes' => $detail->notes
            ];
        });
    }

    public function paymentAssertDatabaseHas($response, $data)
    {
        $this->assertDatabaseHas('payments', [
            'id' => $response->json('data.id'),
            'payment_type' => "CASH"
        ], 'tenant')
            ->assertDatabaseHas('forms', [
                'id' => $response->json('data.form.id')
            ], 'tenant');

        foreach ($data['details'] as $detail) {
            $this->assertDatabaseHas('payment_details', $detail, 'tenant');
        }
    }

    public function getDataPayment($reference)
    {
        $paymentAccount = $this->getChartOfAccountCash();
        $details = [];
        if ($reference::$morphName == 'PaymentOrder') {
            $details = $this->transformPaymentOrderDetails($reference);
        }

        return [
            'date' => date('Y-m-d H:i:s'),
            'increment_group' => date('Ym'),
            'payment_account_id' => $paymentAccount->id,
            'disbursed' => true,
            'paymentable_id' => $reference->id,
            'paymentable_type' => $reference::$morphName,
            'details' => $details,
        ];
    }

    // Test success get all cash outs
    /** @test */
    public function success_get_all_cash_outs()
    {
        $this->success_cash_out_from_payment_order_with_cash_advance_and_account();
        
        $response = $this->json('GET', self::$path . '/payments?join=form,payment_account,details,account,allocation&sort_by=-form.date&fields=payment.*&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_equal=%7B%22payment.payment_type%22:%22cash%22%7D&filter_date_min=%7B%22form.date%22:%22' . date('Y-m-01') . '+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22' . date('Y-m-d') . '+23:59:59%22%7D&limit=10&includes=form%3Bdetails.chartOfAccount%3Bdetails.allocation%3Bpaymentable&page=1', $this->headers);
        $response->assertStatus(200);
    }

    // Test success get a cash out
    /** @test */
    public function success_get_a_cash_out()
    {
        $this->success_cash_out_from_payment_order_without_cash_advance();
        $payment = Payment::orderBy('id', 'desc')->first();
        $data = [
            'includes' => 'form.branch;paymentAccount;details.chartOfAccount;details.allocation'
        ];
        $response = $this->json('GET', self::$path . '/payments/' . $payment->id, $data, $this->headers);
        $response->assertStatus(200);
    }

    // Test create payment order for reference cash out
    /** @test */
    public function success_create_payment_order()
    {
        $data = $this->createPaymentOrder(true);
        $response = $this->json('POST', self::$path . '/payment-orders', $data, $this->headers);
        $response->assertStatus(201);

        $this->assertDatabaseHas('payment_orders', [
            'id' => $response->json('data.id')
        ], 'tenant')
            ->assertDatabaseHas('forms', [
                'id' => $response->json('data.form.id')
            ], 'tenant');

        foreach ($data['details'] as $detail) {
            unset($detail['chart_of_account_name']);
            $this->assertDatabaseHas('payment_order_details', $detail, 'tenant');
        }
    }

    // Test cash out from payment order without cash advance
    /** @test */
    public function success_cash_out_from_payment_order_without_cash_advance()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }

    // Test cash out from payment order with cash advance and account
    /** @test */
    public function success_cash_out_from_payment_order_with_cash_advance_and_account()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);
        $cashAdvance = $this->createCashAdvance($paymentOrder->amount - 10_000);

        $data['cash_advance'] = [
            'id' => $cashAdvance->id,
            'amount' => $cashAdvance->amount_remaining
        ];

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }

    // Test cash out from payment order with cash advance only
    /** @test */
    public function success_cash_out_from_payment_order_with_cash_advance_only()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);
        $cashAdvance = $this->createCashAdvance($paymentOrder->amount);

        $data['cash_advance'] =  [
            'id' => $cashAdvance->id,
            'amount' => $cashAdvance->amount_remaining
        ];

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }
}
