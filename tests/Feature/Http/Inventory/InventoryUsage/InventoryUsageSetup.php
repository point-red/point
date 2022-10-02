<?php 

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\Inventory\InventoryHelper;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;
use App\Model\Master\Allocation;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;
use App\Model\Form;
use App\Model\SettingJournal;
use App\Model\Accounting\ChartOfAccount;
use App\Model\HumanResource\Employee\Employee;

trait InventoryUsageSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;

  public function setUp(): void
  {
    parent::setUp();

    $this->signIn();
    $this->setProject();
    $this->importChartOfAccount();

    $this->tenantUser = TenantUser::find($this->user->id);
    $this->branchDefault = $this->tenantUser->branches()
            ->where('is_default', true)
            ->first();

    $this->setUserWarehouse($this->branchDefault);
  }
  
  private function setUserWarehouse($branch = null)
  {
    $warehouse = $this->createWarehouse($branch);
    $this->tenantUser->warehouses()->syncWithoutDetaching($warehouse->id);
    foreach ($this->tenantUser->warehouses as $warehouse) {
        $warehouse->pivot->is_default = true;
        $warehouse->pivot->save();

        $this->warehouseSelected = $warehouse;
    }
  }

  protected function unsetUserRole()
  {
    $role = Role::createIfNotExists('super admin');
    
    ModelHasRole::where('role_id', $role->id)
      ->where('model_type', 'App\Model\Master\User')
      ->where('model_id', $this->user->id)
      ->delete();
  }

  private function createWarehouse($branch = null)
  {
      $warehouse = new Warehouse();
      $warehouse->name = 'Test warehouse';

      if($branch) $warehouse->branch_id = $branch->id;

      $warehouse->save();

      return $warehouse;
  }

  private function importChartOfAccount()
  {
      Excel::import(new ChartOfAccountImport(), storage_path('template/chart_of_accounts_manufacture.xlsx'));

      $this->artisan('db:seed', [
          '--database' => 'tenant',
          '--class' => 'SettingJournalSeeder',
          '--force' => true,
      ]);

      $settingJournal = SettingJournal::where('feature', 'inventory usage')->where('name', 'difference stock expense')->first();
      $settingJournal->chart_of_account_id = 116;
      $settingJournal->save();
  }
  
  private function createItemWithStocks($unit)
  {
    $item = factory(Item::class)->create();
    $item->units()->save($unit);

    $form = new Form;
    $form->date = now()->toDateTimeString();
    $form->created_by = $this->tenantUser->id;
    $form->updated_by = $this->tenantUser->id;
    $form->save();

    $options = [];
    $options['quantity_reference'] = $item->quantity;
    $options['unit_reference'] = $unit->label;
    $options['converter_reference'] = $unit->converter;

    if ($item->require_expiry_date) {
        $options['expiry_date'] = $item->expiry_date;
    }
    if ($item->require_production_number) {
        $options['production_number'] = $item->production_number;
    }

    InventoryHelper::increase($form, $this->warehouseSelected, $item, 500, $unit->label, 1, $options);

    return $item;
  }
  
  private function getDummyData($itemUnit = 'pcs')
  {
    $warehouse = $this->warehouseSelected;

    $allocation = factory(Allocation::class)->create();
    $employee = factory(Employee::class)->create();
    
    $chartOfAccount = ChartOfAccount::whereHas('type', function ($query) {
      return $query->whereIn('alias', ['BEBAN OPERASIONAL', 'BEBAN NON OPERASIONAL']);
    })->first();

    $unit = new ItemUnit([
      'label' => $itemUnit,
      'name' => $itemUnit,
      'converter' => 1,
    ]);
    $item = $this->createItemWithStocks($unit);

    $role = Role::createIfNotExists('super admin');
    $approver = factory(TenantUser::class)->create();
    $approver->assignRole($role);

    return [
      "increment_group" => date("Ym"),
      "date" => date("Y-m-d H:i:s"),
      "warehouse_id" => $warehouse->id,
      "warehouse_name" => $warehouse->name,
      "employee_id" => $employee->id,
      "employee_name" => $employee->name,
      "request_approval_to" => $approver->id,
      "approver_name" => $approver->name,
      "approver_email" => $approver->email,
      "notes" => null,
      "items" => [
        [
          "item_id" => $item->id,
          "item_name" => $item->name,
          "item_label" => "[{$item->code}] - {$item->name}",
          "chart_of_account_id" => $chartOfAccount->id,
          "chart_of_account_name" => $chartOfAccount->alias,
          "require_expiry_date" => 0,
          "require_production_number" => 0,
          "unit" => $unit->name,
          "converter" => $unit->converter,
          "quantity" => 5,
          "allocation_id" => $allocation->id,
          "allocation_name" => $allocation->name,
          "notes" => null,
          "more" => false,
        ]
      ]
    ];
  }
}