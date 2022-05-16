<?php

namespace Tests\Feature\Http\Finance;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Model\HumanResource\Employee\Employee;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\Payment\Payment;
use App\Model\Master\Branch;
use App\Model\Accounting\CutOff;
use App\Model\Master\User as TenantUser;
use Maatwebsite\Excel\Facades\Excel;

use Tests\TestCase;

class ReportTest extends TestCase
{
    protected $employee = null;
    public static $path = '/api/v1/finance/reports';

    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->setProject();
        $this->importChartOfAccount();
        
        $this->createSampleEmployee();
    }

    private function importChartOfAccount()
    {
        // Artisan::call('tenant:seed:dummy', ['db_name' => env('DB_TENANT_DATABASE')]);

        Excel::import(new ChartOfAccountImport(), storage_path('template/chart_of_accounts_manufacture.xlsx'));

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);
    }

    protected function createSampleEmployee()
    {
        $employee = new Employee;
        $employee->name = 'John Doe';
        $employee->personal_identity = 'PASSPORT 940001930211FA';
        $employee->save();
        $this->employee = $employee;
    }

    public function makePayment($account, $account_detail, $amount_account, $disbursed = false, $date = null)
    {
        if(!$date){
            $date = date('Y-m-d H:i:s');
        }

        $data = [
            'increment_group' => date('Ym'),
            'date'=> $date,
            'due_date'=> $date,
            'payment_type' => "bank",
            'payment_account_id' => $account->id,
            'paymentable_id' => $this->employee->id,
            'paymentable_name' => $this->employee->name,
            'paymentable_type' => "Employee",
            'disbursed' => $disbursed,
            'notes' => null,
            'amount'=> $amount_account,
            'details' => array(
                [
                    'chart_of_account_id'=> $account_detail->id,
                    'amount'=> $amount_account,
                    'allocation_id'=> null,
                    'allocation_name'=> null,
                    'notes'=> "Kas"
                ]
            )
        ];

        $result = Payment::create($data);
    }

    public function makeCutOff($account, $amount_account, $date)
    {
        $data = [
            'increment_group' => date('Ym'),
            'date'=> $date,
            'notes'=> null,
            'details' => array(
                [
                    'chart_of_account_id' => $account->id,
                    'chart_of_account_position' => "DEBIT",
                    'debit' => $amount_account,
                    'credit' => 0,
                    'items' => []
                ]
            )
        ];

        $result = CutOff::createCutoff($data);
    }

    /** @test */
    public function test_report_finance()
    {
        $account = ChartOfAccount::where('alias', 'BCA')->first();
        $account_2 = ChartOfAccount::where('alias', 'MANDIRI')->first();
        $account_detail = ChartOfAccount::where('name', 'OTHER INCOME')->first();
        $account_detail_2 = ChartOfAccount::where('name', 'INTEREST INCOME')->first();
        $this->makeCutOff($account, 500000, date('Y-m-d H:i:s'));
        $this->makeCutOff($account_2, 500000, date('Y-m-d H:i:s', strtotime('-1 day')));
        // $this->makeCutOff($account_detail, 500000, date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->makePayment($account, $account_detail, 10000, false, date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->makePayment($account, $account_detail, 10000, true, date('Y-m-d H:i:s', strtotime('-1 day')));
        $this->makePayment($account, $account_detail, 100000);
        $this->makePayment($account, $account_detail, 100000, true);

        /* s: test get report list */
        $response = $this->json('GET', self::$path.'?filter_form=notArchived%3Bnull&filter_date_min=%7B%22form.date%22:%22'.date('Y-m-d+00:00:00').'%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d+23:59:59').'%22%7D&report_type=bank&limit=1000', array(), $this->headers);
        $response->assertStatus(200);
        /* e: test get report list */

        /* s: test get report list */
        $response = $this->json('GET', self::$path.'?filter_form=notArchived%3Bnull&filter_date_min=%7B%22form.date%22:%22'.date('Y-m-d+00:00:00').'%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d+23:59:59').'%22%7D&report_type=bank&account_id='.$account->id.'&journal_account_id='.$account_detail->id.'&subledger_id='.$this->employee->id.'&subledger_type=Employee&limit=1000', array(), $this->headers);
        $response->assertStatus(200);
        /* e: test get report list */

        /* s: test get report list */
        $response = $this->json('GET', self::$path.'?filter_form=notArchived%3Bnull&filter_date_min=%7B%22form.date%22:%22'.date('Y-m-d+00:00:00').'%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d+23:59:59').'%22%7D&report_type=bank&account_id='.$account->id.'&journal_account_id='.$account_detail_2->id.'&subledger_id='.$this->employee->id.'&subledger_type=Employee&limit=1000', array(), $this->headers);
        $response->assertStatus(200);
        /* e: test get report list */

        /* s: checklist test */
        $payment = Payment::from(Payment::getTableName().' as '.Payment::$alias)
                    ->fields('payment.*')
                    ->sortBy('form.date')
                    ->includes('form;details.chartOfAccount;details.allocation;paymentable');
        $payment = Payment::joins($payment,'form')->first();
        $data = [
            'number' => $payment->form->number,
            'report_name' => 'BankReport',
            'is_checked' => 1
        ];

        $response = $this->json('POST', self::$path.'/set-checklist', $data, $this->headers);
        $response->assertStatus(200);
        /* e: checklist test */
    }

}
