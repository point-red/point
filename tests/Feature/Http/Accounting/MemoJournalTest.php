<?php

namespace Tests\Feature\Http\Accounting;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Master\User as TenantUser;
use App\Model\Accounting\MemoJournal;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Supplier;
use App\Model\Form;
use App\Model\Accounting\Journal;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MemoJournalTest extends TestCase
{
    public static $path = '/api/v1/accounting/memo-journals';

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

    public function dummyData()
    {
        $coa1 = ChartOfAccount::orderBy('id', 'asc')->first();
        $coa2 = ChartOfAccount::orderBy('id', 'desc')->first();
        
        $supplier = factory(Supplier::class)->create();

        $user = new TenantUser;
        $user->name = $this->faker->name;
        $user->address = $this->faker->address;
        $user->phone = $this->faker->phoneNumber;
        $user->email = $this->faker->email;
        $user->save();

        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "request_approval_to" => $user->id,
            "items" => [
                [
                    "chart_of_account_id" => $coa1->id,
                    "chart_of_account_name" => $coa1->name,
                    "masterable_id" => $supplier->id,
                    "masterable_type" => "Supplier",
                    "form_id" => $form->id,
                    "credit" => 0,
                    "debit" => 100000,
                    "notes" => "note 1",
                ],
                [
                    "chart_of_account_id" => $coa2->id,
                    "chart_of_account_name" => $coa2->name,
                    "masterable_id" => null,
                    "masterable_type" => null,
                    "form_id" => null,
                    "credit" => 100000,
                    "debit" => 0,
                    "notes" => "note 2",
                ]
            ]
        ];

        return $data;
    }

    /** @test */
    public function create_memo_journal()
    {
        $data = $this->dummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
    }

    /**
     * @test 
     */
    public function read_all_memo_journal()
    {
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items',
            'fields' => 'memo_journal.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
        ], $this->headers);

        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function read_single_memo_journal()
    {
        $this->create_memo_journal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$memoJournal->id, [
            'includes' => 'items.chart_of_account;items.form;items.masterable;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function update_memo_journal()
    {
        $this->create_memo_journal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["old_id"] = $memoJournal->id;

        $response = $this->json('PATCH', self::$path.'/'.$memoJournal->id, $data, [$this->headers]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function delete_memo_journal()
    {
        $this->create_memo_journal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$memoJournal->id, [], [$this->headers]);

        $response->assertStatus(204);
    }

    /** @test */
    public function export_memo_journal()
    {
        $this->create_memo_journal();

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
        
        $response->assertStatus(200);
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
        
        $response->assertStatus(200);
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
        
        $response->assertStatus(200);
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
        
        $response->assertStatus(200);
    }
}
