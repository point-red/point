<?php 

namespace Tests\Feature\Http\Accounting;

use App\Model\Master\Supplier;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\Accounting\ChartOfAccount;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Form;
use App\Model\Accounting\MemoJournal;

trait MemoJournalSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;

  public function setUp(): void
  {
    parent::setUp();

    $this->signIn();
    $this->setProject();
    $this->importChartOfAccount();
    $this->setUpMemoJournalPermission();
    $_SERVER['HTTP_REFERER'] = 'http://www.example.com/';
  }

  protected function setUpMemoJournalPermission()
  {
    \App\Model\Auth\Permission::createIfNotExists('create memo journal');
    \App\Model\Auth\Permission::createIfNotExists('update memo journal');
    \App\Model\Auth\Permission::createIfNotExists('delete memo journal');
    \App\Model\Auth\Permission::createIfNotExists('read memo journal');
    \App\Model\Auth\Permission::createIfNotExists('approve memo journal');
  }

  protected function setCreatePermission()
  {
    $permission = \App\Model\Auth\Permission::where('name', 'create memo journal')->first();
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setUpdatePermission()
  {
    $permission = \App\Model\Auth\Permission::where('name', 'update memo journal')->first();
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setDeletePermission()
  {
    $permission = \App\Model\Auth\Permission::where('name', 'delete memo journal')->first();
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setReadPermission()
  {
    $permission = \App\Model\Auth\Permission::where('name', 'read memo journal')->first();
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setApprovePermission()
  {
    $permission = \App\Model\Auth\Permission::where('name', 'approve memo journal')->first();
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
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

    private function dummyData()
    {
        $coa1 = ChartOfAccount::orderBy('id', 'asc')->first();
        $coa2 = ChartOfAccount::orderBy('id', 'desc')->first();
        
        $supplier = factory(Supplier::class)->create();

        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "request_approval_to" => $this->user->id,
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

    public function createMemoJournal()
    {
        $this->setCreatePermission();

        $data = $this->dummyData();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        $memoJournal = MemoJournal::where('id', $response->json('data')["id"])->first();
        
        return $memoJournal;
    }
}