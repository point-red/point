<?php

namespace Tests\Feature\Http\Finance;
use Illuminate\Support\Facades\Artisan;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Master\Branch;
use App\Model\Master\User as TenantUser;

use Tests\TestCase;

class CashAdvanceTest extends TestCase
{
    protected $employee = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->createSampleChartAccountType();
        $this->createSampleEmployee();
        $this->signIn();
        $this->setBranch();
    }

    protected function setBranch()
    {
        $branch = new Branch;
        $branch->name = 'CENTRAL';
        $branch->save();

        $tenant = tenant($this->user->id);
        $tenant->branches()->syncWithoutDetaching($branch);
        $tenant->branches()->updateExistingPivot($branch, [
            'is_default' => true,
        ], false);
    }

    protected function createSampleEmployee()
    {
        $employee = new Employee;
        $employee->name = 'John Doe';
        $employee->personal_identity = 'PASSPORT 940001930211FA';
        $employee->save();
        $this->employee = $employee;
    }

    /** @test */
    public function cash_advance_test()
    {
        /* s: get test */
        $response = $this->json('GET', '/api/v1/finance/cash-advances?join=form,details,account,employee&sort_by=-form.number&group_by=cash_advance.id&fields=cash_advance.*&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_date_min=%7B%22form.date%22:%222022-02-01+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%222022-02-26+23:59:59%22%7D&limit=10&includes=employee%3Bform%3Bdetails.account%3B&page=1', array(), [$this->headers]);

        $response->assertStatus(200);
        /* s: get test */

        /* s: create test */
        $data = [
            'increment_group' => '202203',
            'date' => '2022-03-01 15:22:15',
            'payment_type' => 'cash',
            'employee_id' => $this->employee->id,
            'request_approval_to' => $this->user->id,
            'notes' => 'Notes Form',
            'activity' => 'Create Form',
            'details' => array(
                [
                    'chart_of_account_id' => $this->account->id,
                    'amount' => 50000,
                    'notes' => 'Notes'
                ]
            )
        ];

        // API Request
        $response = $this->json('POST', '/api/v1/finance/cash-advances', $data, [$this->headers]);
        
        // Check Status Response
        $response->assertStatus(201);

        $cash_advance = json_decode($response->getContent())->data;
        // Check Database
        $this->assertDatabaseHas('cash_advances', [
            'id' => $cash_advance->id,
        ], 'tenant');

        /* e: create test */

        /* s: show test */
        $response = $this->json('GET', '/api/v1/finance/cash-advances/'.$cash_advance->id.'?includes=employee;form;details.account;form.requestApprovalTo;form.branch', array(), [$this->headers]);

        $response->assertStatus(200);
        /* s: show test */

        /* s: update test */
        $cash_advance = json_decode($response->getContent())->data;

        $data = [
            'number' => $cash_advance->form->number,
            'increment_group' => $cash_advance->form->increment_group,
            'date' => '2022-03-03 15:22:15',
            'payment_type' => $cash_advance->payment_type,
            'employee_id' => $this->employee->id,
            'request_approval_to' => $this->user->id,
            'notes' => 'Notes Form Edited',
            'activity' => 'Update Form',
            'details' => array(
                [
                    'chart_of_account_id' => $this->account->id,
                    'amount' => 20000,
                    'notes' => 'Notes Edited'
                ]
            )
        ];

        // API Request
        $response = $this->json('PATCH', '/api/v1/finance/cash-advances/'.$cash_advance->id, $data, [$this->headers]);
        
        // Check Status Response
        $response->assertStatus(201);

        $cash_advance = json_decode($response->getContent())->data;
        // Check Database
        $this->assertDatabaseHas('cash_advances', [
            'id' => $cash_advance->id,
        ], 'tenant');
        /* e: update test */

        /* s: request cancellation test */
        $response = $this->json('DELETE', "/api/v1/finance/cash-advances/".$cash_advance->id."?id=".$cash_advance->id."&data['reason']=reason&data['activity']=Request Cancellation Form", array(), [$this->headers]);

        $response->assertStatus(204);
        /* e: request cancellation test */

        /* s: cancellation approve test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Approved Cancellation Form'
        ];

        $response = $this->json('POST', "/api/v1/finance/cash-advances/".$cash_advance->id.'/cancellation-approve', $data, [$this->headers]);
        $response->assertStatus(200);
        /* e: cancellation approve test */

        /* s: cancellation reject test */
        $data = [
            'id' => $cash_advance->id,
            'reason' => 'reason',
            'activity' => 'Rejected Cancellation Form'
        ];

        $response = $this->json('POST', "/api/v1/finance/cash-advances/".$cash_advance->id.'/cancellation-reject', $data, [$this->headers]);
        $response->assertStatus(200);
        /* e: cancellation reject test */

        /* s: approve form test */
        $data = [
            'id' => $cash_advance->id,
            'activity' => 'Approved Form'
        ];

        $response = $this->json('POST', "/api/v1/finance/cash-advances/".$cash_advance->id.'/approve', $data, [$this->headers]);
        $response->assertStatus(200);
        /* e: approve form test */

        /* s: reject form test */
        $data = [
            'id' => $cash_advance->id,
            'reason' => 'reason',
            'activity' => 'Rejected Form'
        ];

        $response = $this->json('POST', "/api/v1/finance/cash-advances/".$cash_advance->id.'/reject', $data, [$this->headers]);
        $response->assertStatus(200);
        /* e: reject form test */

    }

}
