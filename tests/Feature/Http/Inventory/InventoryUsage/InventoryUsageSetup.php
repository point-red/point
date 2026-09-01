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
use App\Model\Master\Branch;
use App\User;

trait InventoryUsageSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;

  private $initialItemQuantity = 500;
  private $initialUsageItemQuantity = 5;

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

  protected function createBranch()
  {
      $branch = new Branch();
      $branch->name = 'Test branch';
      $branch->save();

      return $branch;
  }

  protected function createWarehouse($branch = null)
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

      $chartOfAccount = ChartOfAccount::where('name', 'FACTORY DIFFERENCE STOCK EXPENSE')->first();
      $settingJournal = SettingJournal::where('feature', 'inventory usage')->where('name', 'difference stock expense')->first();
      $settingJournal->chart_of_account_id = $chartOfAccount->id;
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
    $options['expiry_date'] = null;
    $options['production_number'] = null;

    InventoryHelper::increase($form, $this->warehouseSelected, $item, $this->initialItemQuantity, $unit->label, 1, $options);

    return $item;
  }

  private function createItemDnaWithStocks($unit)
  {
    $item = factory(Item::class)->create([
      "require_expiry_date" => true,
      "require_production_number" => true
    ]);
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
    $options['expiry_date'] = date('Y-m-31 23:59:59');
    $options['production_number'] = 'TEST001';

    InventoryHelper::increase($form, $this->warehouseSelected, $item, $this->initialItemQuantity, $unit->label, 1, $options);

    return [$item, $options];
  }

  private function changeUserDefaultBranch($newBranch = null)
  {
      $this->tenantUser->branches()->syncWithoutDetaching($newBranch);
      foreach ($this->tenantUser->branches as $branch) {
        if ($newBranch->id === $branch->id) {
          $branch->pivot->is_default = true;
          $branch->pivot->save();
        }
      }
  }

  private function changeActingAs($tenantUser, $inventoryUsage)
  {
      $tenantUser->branches()->syncWithoutDetaching($inventoryUsage->form->branch_id);
      foreach ($tenantUser->branches as $branch) {
          $branch->pivot->is_default = true;
          $branch->pivot->save();
      }
      $tenantUser->warehouses()->syncWithoutDetaching($inventoryUsage->warehouse_id);
      foreach ($tenantUser->warehouses as $warehouse) {
          $warehouse->pivot->is_default = true;
          $warehouse->pivot->save();
      }
      $user = new User();
      $user->id = $tenantUser->id;
      $user->name = $tenantUser->name;
      $user->email = $tenantUser->email;
      $user->save();
      $this->actingAs($user, 'api');
  }

  private function getDummyDataItem($isItemDna = false)
  {
    $allocation = factory(Allocation::class)->create();
    $chartOfAccount = ChartOfAccount::whereHas('type', function ($query) {
      return $query->whereIn('alias', ['BEBAN OPERASIONAL', 'BEBAN NON OPERASIONAL']);
    })->first();
    $unit = new ItemUnit(['label' => 'pcs', 'name' => 'pcs', 'converter' => 1]);

    $quantity = $this->initialItemQuantity;

    if ($isItemDna) {
      $createdItemDna = $this->createItemDnaWithStocks($unit);
      $item = $createdItemDna[0];
      $itemDna = [
        "quantity" => $quantity,
        "expiry_date" => convert_to_server_timezone($createdItemDna[1]["expiry_date"], 'UTC', 'asia/jakarta'),
        "production_number" => $createdItemDna[1]["production_number"],
      ];
    } else {
      $item = $this->createItemWithStocks($unit);
    }

    $usageItem = [
      "item_id" => $item->id,
      "item_name" => $item->name,
      "item_label" => "[{$item->code}] - {$item->name}",
      "chart_of_account_id" => $chartOfAccount->id,
      "chart_of_account_name" => $chartOfAccount->alias,
      "require_expiry_date" => 0,
      "require_production_number" => 0,
      "unit" => $unit->name,
      "converter" => $unit->converter,
      "quantity" => $quantity,
      "allocation_id" => $allocation->id,
      "allocation_name" => $allocation->name,
      "notes" => null,
      "more" => false,
    ];

    if ($isItemDna) {
      $usageItem['dna'] = [$itemDna];
    }

    return $usageItem;
  }
  
  private function getDummyData($inventoryUsage = null, $itemUnit = 'pcs', $isItemDna = false)
  {
    $warehouse = $this->warehouseSelected;
    $unit = new ItemUnit([
      'label' => $itemUnit,
      'name' => $itemUnit,
      'converter' => 1,
    ]);
    $quantity = $this->initialUsageItemQuantity;

    if ($inventoryUsage) {
      $inventoryUsageItem = $inventoryUsage->items()->first();

      $employee = $inventoryUsage->employee;

      $allocation = $inventoryUsageItem->allocation;
      $chartOfAccount = $inventoryUsageItem->account;
      $item = $inventoryUsageItem->item;

      if ($itemUnit === 'pcs') {
        $unit = ItemUnit::where('label', $inventoryUsageItem->unit)->first();
      }
      
      $quantity = $inventoryUsageItem->quantity;

      $approver = $inventoryUsage->form->requestApprovalTo;
    } else {
      $allocation = factory(Allocation::class)->create();
      $employee = factory(Employee::class)->create();
      
      $chartOfAccount = ChartOfAccount::whereHas('type', function ($query) {
        return $query->whereIn('alias', ['BEBAN OPERASIONAL', 'BEBAN NON OPERASIONAL']);
      })->first();
  
      if ($isItemDna) {
        $createdItemDna = $this->createItemDnaWithStocks($unit);
        $item = $createdItemDna[0];
        $itemDna = [
          "quantity" => $quantity,
          "expiry_date" => convert_to_server_timezone($createdItemDna[1]["expiry_date"], 'UTC', 'asia/jakarta'),
          "production_number" => $createdItemDna[1]["production_number"],
        ];

      } else {
        $item = $this->createItemWithStocks($unit);
      }
  
      $role = Role::createIfNotExists('super admin');
      $approver = factory(TenantUser::class)->create();
      $approver->assignRole($role);
    }

    $usageItem = [
          "item_id" => $item->id,
          "item_name" => $item->name,
          "item_label" => "[{$item->code}] - {$item->name}",
          "chart_of_account_id" => $chartOfAccount->id,
          "chart_of_account_name" => $chartOfAccount->alias,
          "require_expiry_date" => 0,
          "require_production_number" => 0,
          "unit" => $unit->name,
          "converter" => $unit->converter,
          "quantity" => $quantity,
          "allocation_id" => $allocation->id,
          "allocation_name" => $allocation->name,
          "notes" => null,
          "more" => false,
    ];
    if ($isItemDna) {
      $usageItem['dna'] = [$itemDna];
    }
    
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
      "items" => [$usageItem]
    ];
  }
}
