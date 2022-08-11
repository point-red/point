<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Accounting\MemoJournal;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Form;
use App\Model\Accounting\Journal;
use Tests\TestCase;

class MemoJournalTest extends TestCase
{
    use MemoJournalSetup;

    public static $path = '/api/v1/accounting/memo-journals';

    /** @test */
    public function unauthorized_create_memo_journal()
    {
        $data = $this->dummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /** @test */
    public function invalid_data_create_memo_journal()
    {
        $this->setCreatePermission();
        
        $data = $this->dummyData();

        $data = data_set($data, 'date', null);
        $data = data_set($data, 'request_approval_to', null);
        $data = data_set($data, 'items.0.chart_of_account_id', null);
        $data = data_set($data, 'items.0.debit', null);
        $data = data_set($data, 'items.0.credit', null);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function negative_debit_credit_create_memo_journal()
    {
        $this->setCreatePermission();
        
        $data = $this->dummyData();

        $data = data_set($data, 'items.0.debit', -100000);
        $data = data_set($data, 'items.1.credit', -100000);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function debit_credit_not_balance_create_memo_journal()
    {
        $this->setCreatePermission();
        
        $data = $this->dummyData();

        $data = data_set($data, 'items.0.debit', 200000);
        $data = data_set($data, 'items.1.credit', 300000);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Total debit and credit not balance."
            ]);
    }

    /** @test */
    public function success_create_memo_journal()
    {
        $this->setCreatePermission();

        $data = $this->dummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /**
     * @test 
     */
    public function unauthorized_read_all_memo_journal()
    {   
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items',
            'fields' => 'memo_journal.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
        ], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }
    
    /**
     * @test 
     */
    public function success_read_all_memo_journal()
    {
        $this->setReadPermission();

        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();
        
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items',
            'fields' => 'memo_journal.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "id" => $memoJournal->id,
                        "form" => [
                            "id" => $memoJournal->form->id,
                            "number" => $memoJournal->form->number
                        ],
                    ]
                ]
            ]);
    }

    /**
     * @test 
     */
    public function unauthorized_read_single_memo_journal()
    {   
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$memoJournal->id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /**
     * @test 
     */
    public function invalid_id_read_single_memo_journal()
    {   
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $random_id = $memoJournal->id + 1000;

        $response = $this->json('GET', self::$path.'/'.$random_id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }
    
    /**
     * @test 
     */
    public function success_read_single_memo_journal()
    {
        $this->setReadPermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$memoJournal->id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $memoJournal->id,
                    "form" => [
                        "id" => $memoJournal->form->id,
                        "number" => $memoJournal->form->number
                    ],
                ]
            ]);
    }

    /** @test */
    public function unauthorized_update_memo_journal()
    {   
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /** @test */
    public function invalid_id_update_memo_journal()
    {   
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $random_id = $memoJournal->id + 1000;

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;

        $response = $this->json('PATCH', self::$path.'/'.$random_id, $data, [$this->headers]);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /** @test */
    public function negative_debit_credit_update_memo_journal()
    {
        $this->setUpdatePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;
        $data["notes"] = "Edit notes";
        $data = data_set($data, 'items.0.debit', -100000);
        $data = data_set($data, 'items.1.credit', -100000);

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function debit_credit_not_balance_update_memo_journal()
    {
        $this->setUpdatePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;
        $data["notes"] = "Edit notes";
        $data = data_set($data, 'items.0.debit', 200000);
        $data = data_set($data, 'items.1.credit', 300000);

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Total debit and credit not balance."
            ]);
    }

    /** @test */
    public function invalid_data_update_memo_journal()
    {
        $this->setUpdatePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;
        $data["notes"] = "Edit notes";

        $data = data_set($data, 'date', null);
        $data = data_set($data, 'request_approval_to', null);
        $data = data_set($data, 'items.0.chart_of_account_id', null);
        $data = data_set($data, 'items.0.debit', null);
        $data = data_set($data, 'items.0.credit', null);

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function success_update_memo_journal()
    {
        $this->setUpdatePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;
        $data["notes"] = "Edit notes";

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(201);

        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'notes' => "Edit notes",
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_delete_memo_journal()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$memoJournal->id, [], [$this->headers]);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /** @test */
    public function invalid_id_delete_memo_journal()
    {        
        $this->setDeletePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $random_id = $memoJournal->id + 1000;

        $response = $this->json('DELETE', self::$path.'/'.$random_id, [], [$this->headers]);

        $response->assertStatus(404)
            ->assertJson([
                "code" => 404,
                "message" => "Model not found."
            ]);
    }

    /** @test */
    public function success_delete_memo_journal()
    {        
        $this->setDeletePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$memoJournal->id, [], [$this->headers]);

        $response->assertStatus(204);
    }

    /** @test */
    public function failed_export_delivery_order()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $headers = $this->headers;
        unset($headers['Tenant']);

        $data = [
            "data" => [
                "ids" => [$memoJournal->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days")),
                "tenant_name" => "development"
            ]
        ];

        $response = $this->json('POST', self::$path.'/export', $data, $headers);

        $response->assertStatus(500);
    }

    /** @test */
    public function success_export_memo_journal()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = [
            "data" => [
                "ids" => [$memoJournal->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days")),
                "tenant_name" => "development"
            ]
        ];

        $response = $this->json('POST', self::$path.'/export', $data, $this->headers);
        
        $response->assertStatus(200)->assertJsonStructure([ 'data' => ['url'] ]);
    }

    /** @test */
    public function form_references()
    {
        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->number = 'FORM001';
        $form->save();

        $coa = ChartOfAccount::orderBy('id', 'asc')->first();

        $journal = new Journal;
        $journal->form_id = $form->id;
        $journal->journalable_type = 'Supplier';
        $journal->journalable_id = 1;
        $journal->chart_of_account_id = $coa->id;
        $journal->debit = 10000;
        $journal->credit = 0;
        $journal->save();

        $response = $this->json('GET', self::$path.'/form-references', [
            'coa_id' => $coa->id,
            'masterable_id' => 1,
            'masterable_type' => 'Supplier',
            'filter_like' => $form->number,
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "id" => $form->id,
                        "number" => $form->number
                    ]
                ]
            ]);
    }

    /** @test */
    public function data_form_references()
    {
        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        $coa = ChartOfAccount::orderBy('id', 'asc')->first();

        $journal = new Journal;
        $journal->form_id = $form->id;
        $journal->journalable_type = 'Supplier';
        $journal->journalable_id = 1;
        $journal->chart_of_account_id = $coa->id;
        $journal->debit = 10000;
        $journal->credit = 0;
        $journal->save();

        $response = $this->json('GET', self::$path.'/data-form-references', [
            'coa_id' => $coa->id,
            'form_id' => $form->id,
            'master_id' => 1,
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $journal->id,
                    "form_id" => $form->id,
                    "chart_of_account_id" => $coa->id,
                    "journalable_id" => $journal->journalable_id,
                    "journalable_type" => $journal->journalable_type
                ]
            ]);
    }

    /** @test */
    public function data_form_references_without_master()
    {
        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        $coa = ChartOfAccount::orderBy('id', 'asc')->first();

        $journal = new Journal;
        $journal->form_id = $form->id;
        $journal->journalable_type = 'Supplier';
        $journal->journalable_id = 1;
        $journal->chart_of_account_id = $coa->id;
        $journal->debit = 10000;
        $journal->credit = 0;
        $journal->save();

        $response = $this->json('GET', self::$path.'/data-form-references', [
            'coa_id' => $coa->id,
            'form_id' => $form->id,
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $journal->id,
                    "form_id" => $form->id,
                    "chart_of_account_id" => $coa->id,
                    "journalable_id" => $journal->journalable_id,
                    "journalable_type" => $journal->journalable_type
                ]
            ]);
    }
}
