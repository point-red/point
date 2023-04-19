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

trait CashOutSetup
{
    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->setRole();
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
        $paymentOrder = PaymentOrder::create($form);
        // Approve payment order
        $paymentOrder->form->approval_by = auth()->user()->id;
        $paymentOrder->form->save();

        return $paymentOrder;
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
        $this->makePaymentCashIn($paymentAccount, $reference->amount);
        $details = [];
        if ($reference::$morphName == 'PaymentOrder') {
            $details = $this->transformPaymentOrderDetails($reference);
        }

        return [
            'amount' => $reference->amount,
            'payment_type' => $reference->payment_type,
            'date' => date('Y-m-d H:i:s'),
            'increment_group' => date('Ym'),
            'payment_account_id' => $paymentAccount->id,
            'disbursed' => true,
            'paymentable_id' => $reference->paymentable_id,
            'paymentable_type' => $reference->paymentable_type,
            'referenceable_type' => $reference::$morphName,
            'referenceable_id' => $reference->id,
            'details' => $details,
        ];
    }

    // Copied from CashAdvanceTest
    public function makePaymentCashIn($account, $amount_account)
    {
        $paymentable = factory(Customer::class)->create();
        $account_detail = ChartOfAccount::where('name', 'OTHER INCOME')->first();
        // s: insert cash in
        $data = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s'),
            'payment_type' => "cash",
            'payment_account_id' => $account->id,
            'paymentable_id' => $paymentable->id,
            'paymentable_name' => $paymentable->name,
            'paymentable_type' => $paymentable::$morphName,
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

        Payment::create($data);
        // e: insert cash in
    }
}
