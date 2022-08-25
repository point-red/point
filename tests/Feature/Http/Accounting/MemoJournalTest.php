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

        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
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

        $this->assertDatabaseHas('memo_journals', [
            'id' => $response->json('data.id')
        ], 'tenant');

        foreach ($data['items'] as $item) {
            $this->assertDatabaseHas('memo_journal_items', $item, 'tenant');
        }

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

        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }
    
    /**
     * @test 
     */
    public function success_read_all_memo_journal()
    {
        $this->setReadPermission();

        $this->createMemoJournal();

        $memoJournals = MemoJournal::get();
        $memoJournals = $memoJournals->sortByDesc(function($q){
            return $q->form->number;
        });
        
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items',
            'fields' => 'memo_journal.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);

        $data = [];
        foreach ($memoJournals as $memoJournal) {
            $items = [];
            foreach ($memoJournal->items as $item) {
                array_push($items, [
                    "id" => $item->id,
                    "memo_journal_id" => $item->memo_journal_id,
                    "chart_of_account_id" => $item->chart_of_account_id,
                    "chart_of_account_name" => $item->chart_of_account_name,
                    "form_id" => $item->form_id,
                    "masterable_id" => $item->masterable_id,
                    "masterable_type" => $item->masterable_type,
                    "debit" => $item->debit,
                    "credit" => $item->credit,
                    "notes" => $item->notes,
                ]);
            }
            array_push($data, [
                "id" => $memoJournal->id,
                "form" => [
                    "id" => $memoJournal->form->id,
                    "date" => $memoJournal->form->date,
                    "number" => $memoJournal->form->number,
                    "id" => $memoJournal->form->id,
                    "notes" => $memoJournal->form->notes,
                ],
                "items" => $items
            ]);
        };

        $response->assertStatus(200)
            ->assertJson([
                "data" => $data
            ]);
    }

    /**
     * @test 
     */
    public function unauthorized_read_single_memo_journal()
    {   
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('GET', self::$path.'/'.$memoJournal->id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }

    /**
     * @test 
     */
    public function invalid_id_read_single_memo_journal()
    {   
        $this->setReadPermission();

        $memoJournal = $this->createMemoJournal();

        $random_id = $memoJournal->id + 1000;

        $response = $this->json('GET', self::$path.'/'.$random_id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(404)
            ->assertJson([
                "code" => 404,
                "message" => "Model not found."
            ]);
    }
    
    /**
     * @test 
     */
    public function success_read_single_memo_journal()
    {
        $this->setReadPermission();
        
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('GET', self::$path.'/'.$memoJournal->id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);

        $items = [];
        foreach ($memoJournal->items as $item) {
            array_push($items, [
                "id" => $item->id,
                "memo_journal_id" => $item->memo_journal_id,
                "chart_of_account_id" => $item->chart_of_account_id,
                "chart_of_account_name" => $item->chart_of_account_name,
                "form_id" => $item->form_id,
                "masterable_id" => $item->masterable_id,
                "masterable_type" => $item->masterable_type,
                "debit" => $item->debit,
                "credit" => $item->credit,
                "notes" => $item->notes,
            ]);
        }
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $memoJournal->id,
                    "form" => [
                        "id" => $memoJournal->form->id,
                        "date" => $memoJournal->form->date,
                        "number" => $memoJournal->form->number,
                        "id" => $memoJournal->form->id,
                        "notes" => $memoJournal->form->notes,
                    ],
                    "items" => $items
                ]
            ]);
    }

    /** @test */
    public function unauthorized_update_memo_journal()
    {   
        $memoJournal = $this->createMemoJournal();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }

    /** @test */
    public function invalid_id_update_memo_journal()
    {   
        $this->setUpdatePermission();
        
        $memoJournal = $this->createMemoJournal();

        $random_id = $memoJournal->id + 1000;

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;

        $response = $this->json('PATCH', self::$path.'/'.$random_id, $data, [$this->headers]);
        
        $response->assertStatus(404)
            ->assertJson([
                "code" => 404,
                "message" => "Model not found."
            ]);
    }

    /** @test */
    public function negative_debit_credit_update_memo_journal()
    {
        $this->setUpdatePermission();
        
        $memoJournal = $this->createMemoJournal();

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
        
        $memoJournal = $this->createMemoJournal();

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
        
        $memoJournal = $this->createMemoJournal();

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
        
        $memoJournal = $this->createMemoJournal();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;
        $data["notes"] = "Edit notes";

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $items = [];
        foreach ($data['items'] as $item) {
            array_push($items, [
                "memo_journal_id" => $response->json('data.id'),
                "chart_of_account_id" => $item['chart_of_account_id'],
                "chart_of_account_name" => $item['chart_of_account_name'],
                "form_id" => $item['form_id'],
                "masterable_id" => $item['masterable_id'],
                "masterable_type" => $item['masterable_type'],
                "debit" => $item['debit'],
                "credit" => $item['credit'],
                "notes" => $item['notes'],
            ]);
        }
        
        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "id" => $response->json('data.id'),
                    "form" => [
                        "id" => $response->json('data.form.id'),
                        "date" => $data["date"],
                        "number" => $response->json('data.form.number'),
                        "notes" => $data["notes"],
                    ],
                    "items" => $items
                ]
            ]);
        
        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => null,
            'edited_number' => $response->json('data.form.number'),
            'notes' => $memoJournal->form->notes,
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');

        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'edited_number' => null,
            'notes' => $data["notes"],
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_delete_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('DELETE', self::$path.'/'.$memoJournal->id, [], [$this->headers]);

        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }

    /** @test */
    public function invalid_id_delete_memo_journal()
    {        
        $this->setDeletePermission();
        
        $memoJournal = $this->createMemoJournal();

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
        
        $memoJournal = $this->createMemoJournal();

        $reason = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path.'/'.$memoJournal->id, ['reason' => $reason], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'request_cancellation_to' => $memoJournal->form->request_approval_to,
            'request_cancellation_by' => $this->user->id,
            'request_cancellation_reason' => $reason,
            'cancellation_status' => 0,
        ], 'tenant');
        
    }

    /** @test */
    public function failed_export_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

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
        $memoJournal = $this->createMemoJournal();

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
