<?php

namespace Tests\Feature\Http\Finance;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Finance\Payment\Payment;
use App\Model\Accounting\Journal;
use App\Model\Master\Branch;
use App\Model\Token;
use App\Model\Master\User as TenantUser;
use Maatwebsite\Excel\Facades\Excel;

use Tests\TestCase;

class CashAdvanceTest extends TestCase
{
    protected $employee = null;
    public static $path = '/api/v1/finance/cash-advances';

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

    public function makePaymentCashIn($account, $account_detail, $amount_account)
    {
        // s: insert cash in
        $data = [
            'increment_group' => date('Ym'),
            'date'=> date('Y-m-d H:i:s'),
            'due_date'=> date('Y-m-d H:i:s'),
            'payment_type' => "cash",
            'payment_account_id' => $account->id,
            'paymentable_id' => $this->employee->id,
            'paymentable_name' => $this->employee->name,
            'paymentable_type' => "Employee",
            'disbursed' => false,
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
        // $response = $this->json('POST', '/api/v1/finance/payments', $data, $this->headers);
        // $response->assertStatus(201);
        // e: insert cash in
    }

    public function addAccountBalance($position, $new_balance)
    {
        $account = ChartOfAccount::where('name', 'CASH')->first();

        $journal = new Journal;
        $journal->form_id = $account->id;
        $journal->chart_of_account_id = $account->id;
        if($position == 'debit'){
            $journal->debit = $new_balance;
        }else{
            $journal->credit = $new_balance;
        }
        $journal->save();
    }

    public function createSampleCashAdvance($amount, $amount_account = null)
    {
        $account = ChartOfAccount::where('name', 'CASH')->first();
        $account_detail = ChartOfAccount::where('name', 'OTHER INCOME')->first();
        if($amount_account){
            $this->makePaymentCashIn($account, $account_detail, $amount_account);
        }

        //create sample cash advance
        $data = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'payment_type' => 'cash',
            'employee_id' => $this->employee->id,
            'request_approval_to' => $this->user->id,
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
        return $data;

    }

    public function createSampleUpdateCashAdvance($cash_advance, $amount)
    {
        //create sample cash advance
        $data = [
            'id' =>$cash_advance->id,
            'number' =>$cash_advance->form->number,
            'increment_group' => $cash_advance->form->increment_group,
            'date' => date('Y-m-d H:i:s'),
            'payment_type' => 'cash',
            'employee_id' => $this->employee->id,
            'request_approval_to' => $this->user->id,
            'notes' => 'Notes Form',
            'amount' => $amount,
            'activity' => 'Updated',
            'details' => array(
                [
                    'chart_of_account_id' => $cash_advance->details[0]->account->id,
                    'amount' => $amount,
                    'notes' => 'Notes'
                ]
            )
        ];
        return $data;
    }

    /** @test */
    public function test_cash_advance()
    {
        /* s: test fail because balance not enough */
        $data = $this->createSampleCashAdvance(5000, 2000);
        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(422);
        /* e: test fail because balance not enough */

        /* s: test store success */
        $data = $this->createSampleCashAdvance(5000, 100000);
        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
        $cash_advance = json_decode($response->getContent())->data;
        /* e: test store success */

        /* s: test get cash advance list */
        $response = $this->json('GET', self::$path.'?join=form,details,account,employee&sort_by=-form.number&group_by=cash_advance.id&fields=cash_advance.*&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_date_min=%7B%22form.date%22:%22'.date('Y-m-01').'+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22'.date('Y-m-d').'+23:59:59%22%7D&limit=10&includes=employee%3Bform%3Bdetails.account%3B&page=1', array(), $this->headers);
        $response->assertStatus(200);
        /* e: test get cash advance list */

        /* s: show cash advance */
        $response = $this->json('GET', self::$path.'/'.$cash_advance->id.'?includes=employee;form;details.account;form.requestApprovalTo;form.branch', array(), $this->headers);
        $response->assertStatus(200);
        /* e: show cash advance */

        /* s: reject test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Rejected'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/reject', $data, $this->headers);
        $response->assertStatus(200);
        /* e: reject test */

        /* s: request cancellation test */
        $data = [
            'id' => $cash_advance->id,
            'reason' => 'Reason',
            'activity' => 'Request Cancellation'
        ];
        $response = $this->json('DELETE', self::$path.'/'.$cash_advance->id, $data, $this->headers);
        $response->assertStatus(204);
        /* e: request cancellation test */

        /* s: cancellation approve test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Cancellation Approve'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/cancellation-approve', $data, $this->headers);
        $response->assertStatus(200);
        /* e: cancellation approve test */

        /* s: cancellation reject test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Cancellation Rejected'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/cancellation-reject', $data, $this->headers);
        $response->assertStatus(200);
        /* e: cancellation reject test */

        /* s: history cash advance */
        $response = $this->json('GET', self::$path.'/history?sort_by=-date&group_by=user_activity.id&fields=user_activity.*&filter_equal=%7B%22number%22:%22'.$cash_advance->form->number.'%22%7D&filter_like=%7B%7D&limit=10&includes=user%3Btable.employee%3Btable.form%3Btable.details.account%3B&page=1', array(), $this->headers);
        $response->assertStatus(200);
        /* e: history cash advance */

        /* s: store history test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Print'
        ];

        $response = $this->json('POST', self::$path.'/history', $data, $this->headers);
        $response->assertStatus(204);
        /* e: store history test */

        /* s: update cash advance */
        $data = $this->createSampleUpdateCashAdvance($cash_advance, 7000);
        $response = $this->json('PATCH', self::$path.'/'.$cash_advance->id, $data, $this->headers);
        $response->assertStatus(201);
        $cash_advance = json_decode($response->getContent())->data;
        /* e: update cash advance */

        /* s: send request approval email test */
        $data = [
            'bulk_id'=> array($cash_advance->id),
            'tenant_url' => 'http://dev.localhost:8080',
            'activity' => 'Request approve all'
        ];

        $response = $this->json('POST', self::$path.'/send-bulk-request-approval', $data, $this->headers);
        $response->assertStatus(204);
        /* e: send request approval email test */

        /* s: approval email fail test */
        $data = [
            'token' => 'NG4WUR', 
            'id' => $cash_advance->id, 
            'status' => 1, 
            'activity' => 'approved by email'
        ];

        $response = $this->json('POST', '/api/v1/approval-with-token/finance/cash-advances', $data, $this->headers);
        $response->assertStatus(422);
        /* e: approval email fail  test */

        /* s: approval email test */
        $token = Token::take(1)->first();
        $data = [
            'token' => $token->token, 
            'id' => $cash_advance->id, 
            'status' => -1, 
            'activity' => 'approved by email'
        ];

        $response = $this->json('POST', '/api/v1/approval-with-token/finance/cash-advances', $data, $this->headers);
        $response->assertStatus(200);
        /* e: approval email test */

        /* s: fail bulk approval email test */
        $data = $this->createSampleCashAdvance(5000);
        $response = $this->json('POST', self::$path, $data, $this->headers);
        $cash_advance = json_decode($response->getContent())->data;

        $data = [
            'token' => 'NG4WUR', 
            'bulk_id' => array($cash_advance->id), 
            'status' => -1, 
            'activity' => 'approved by email'
        ];

        $response = $this->json('POST', '/api/v1/approval-with-token/finance/cash-advances/bulk', $data, $this->headers);
        $response->assertStatus(422);
        /* e: fail bulk approval email test */

        /* s: bulk approval email test */
        $token = Token::take(1)->first();
        $data = [
            'token' => $token->token, 
            'bulk_id' => array($cash_advance->id), 
            'status' => -1, 
            'activity' => 'approved by email'
        ];

        $response = $this->json('POST', '/api/v1/approval-with-token/finance/cash-advances/bulk', $data, $this->headers);
        $response->assertStatus(200);
        /* e: bulk approval email test */

        /* s: approve test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Approved'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/approve', $data, $this->headers);
        $response->assertStatus(200);
        /* e: approve test */

        /* s: approve fail test */
        $this->addAccountBalance('credit',100000);
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Approved'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/approve', $data, $this->headers);
        $response->assertStatus(422);
        /* e: approve fail test */

        /* s: refund test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Refunded'
        ];

        $response = $this->json('POST', self::$path.'/'.$cash_advance->id.'/refund', $data, $this->headers);
        $response->assertStatus(200);
        /* e: refund test */


    }

}
