<?php 

namespace Tests\Feature\Http\Accounting;

use App\Model\Master\Supplier;
use App\Model\Master\User as TenantUser;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\Accounting\ChartOfAccount;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Form;

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
    $_SERVER['HTTP_REFERER'] = 'http://www.example.com/';
  }

  protected function setCreatePermission()
  {
    $permission = \App\Model\Auth\Permission::createIfNotExists('create memo journal');
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setUpdatePermission()
  {
    $permission = \App\Model\Auth\Permission::createIfNotExists('update memo journal');
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setDeletePermission()
  {
    $permission = \App\Model\Auth\Permission::createIfNotExists('delete memo journal');
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setReadPermission()
  {
    $permission = \App\Model\Auth\Permission::createIfNotExists('read memo journal');
    $hasPermission = new \App\Model\Auth\ModelHasPermission();
    $hasPermission->permission_id = $permission->id;
    $hasPermission->model_type = 'App\Model\Master\User';
    $hasPermission->model_id = $this->user->id;
    $hasPermission->save();
  }

  protected function setApprovePermission()
  {
    $permission = \App\Model\Auth\Permission::createIfNotExists('approve memo journal');
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

    public function createMemoJournal()
    {
        $this->setCreatePermission();

        $data = $this->dummyData();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        return $response->json('data');
    }
}