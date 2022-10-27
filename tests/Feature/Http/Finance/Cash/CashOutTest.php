<?php

namespace Tests\Feature\Http\Finance\Cash;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CashOutTest extends TestCase
{
    public static $path = '/api/v1/finance';

    protected $references = [
        'PaymentOrder',
        'PurchaseDownPayment',
        'SalesDownPayment'
    ];

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

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);
    }

    // Create paymentable
    public function createPaymentable()
    {
        $paymentableOptions = [
            'Supplier' => 'Supplier',
            'Customer' => 'Customer',
            'Employee' => 'Employee'
        ];

        $randomPaymentable = array_rand($paymentableOptions);
        switch ($randomPaymentable) {
            case 'Supplier':
                $paymentable = factory(Supplier::class)->create();
                $paymentable->morphName = Supplier::$morphName;
                break;

            case 'Customer':
                $paymentable = factory(Customer::class)->create();
                $paymentable->morphName = Customer::$morphName;
                break;

            case 'Employee':
                $paymentable = factory(Employee::class)->create();
                $paymentable->morphName = Employee::$morphName;
                break;
        }

        return $paymentable;
    }

    // Get chartOfAccount
    public function getChartOfAccount()
    {
        $chartOfAccount = ChartOfAccount::join(
            ChartOfAccountType::getTableName() . ' as ' . ChartOfAccountType::$alias,
            ChartOfAccount::getTableName() . '.type_id',
            '=',
            ChartOfAccountType::$alias . '.id'
        )
            // ->where(ChartOfAccountType::$alias . '.name', 'CASH')
            ->get()
            ->random();

        return $chartOfAccount;
    }

    // Create data payment order
    public function createDataPaymentOrder()
    {
        $paymentable = $this->createPaymentable();

        $data = [
            'payment_type' => 'CASH',
            'paymentable_id' => $paymentable->id,
            'paymentable_type' => $paymentable->morphName,
            'details' => [
                [
                    'chart_of_account_id' => $this->getChartOfAccount()->id,
                    'amount' => rand(10000, 1000000)
                ]
            ]
        ];

        return $data;
    }

    // Create data cash out
    public function createDataCashOut($reference)
    {
        $chartOfAccount = $this->getChartOfAccount();

        $data = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'payment_type' => "CASH",
            'payment_account_id' => $chartOfAccount->id,
            'paymentable_id' => $reference->paymentable_id,
            'paymentable_name' => $reference->paymentable_name,
            'paymentable_type' => $reference->paymentable_type,
            'disbursed' => false,
            'notes' => null,
            'amount' => $reference->amount,
            'details' => array(
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'amount' => $reference->amount,
                    'allocation_id' => null,
                    'allocation_name' => null,
                    'notes' => "Kas"
                ]
            )
        ];

        return $data;
    }

    // Create cash out
    public function createCashOut($reference)
    {
        switch ($reference) {
            case 'PaymentOrder':
                $dataPaymentOrder = $this->createDataPaymentOrder();
                $payment = PaymentOrder::create($dataPaymentOrder);
                break;

            default:
                # code...
                break;
        }

        $data = $this->createDataCashOut($payment);

        return $data;
    }

    // Test cash out from payment order
    /** @test */
    public function success_cash_out_from_payment_order()
    {
        $data = $this->createCashOut('PaymentOrder');

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);

        $response
            ->assertStatus(201);
        $createdCashOut = json_decode($response->getContent())->data;
    }

    // Test get all cashs
    /** @test */
    public function get_all_cashs()
    {
        $response = $this->json('GET', self::$path . '/payments?join=form,payment_account,details,account,allocation&sort_by=-form.date&fields=payment.*&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_equal=%7B%22payment.payment_type%22:%22cash%22%7D&filter_date_min=%7B%22form.date%22:%22' . date('Y-m-01') . '%22%7D&filter_date_max=%7B%22form.date%22:%22' . date('Y-m-d') . '%22%7D&limit=10&includes=form%3Bdetails.chartOfAccount%3Bdetails.allocation%3Bpaymentable&page=1', array(), $this->headers);
        $response->assertStatus(200);
    }
}
