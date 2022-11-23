<?php 

namespace Tests\Feature\Http\Purchase\Request;

use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;

use App\Model\Master\Item;
use App\Model\Auth\Permission;
use App\Model\Master\Allocation;
use App\Model\Master\User as TenantUser;

trait PurchaseRequestSetup {

  public static $path = '/api/v1/purchase/requests';
  protected $item = null;
  protected $allocation = null;
  protected $purchase = null;

  public function setUp(): void
  {
      parent::setUp();
      $this->signIn();
      $this->setProject();
      $this->setPurchaseRequestPermission();
      $this->createSampleChartAccountType();
      $this->createSampleEmployee();
      $this->createSampleItem();
      $this->createSampleAllocation();
  }

  protected function setPurchaseRequestPermission()
  {
      $this->setRole();
      Permission::createIfNotExists('menu purchase');

      $permission = ['purchase request'];

      foreach ($permission as $permission) {
          Permission::createIfNotExists('create '.$permission);
          Permission::createIfNotExists('read '.$permission);
          Permission::createIfNotExists('update '.$permission);
          Permission::createIfNotExists('delete '.$permission);
          Permission::createIfNotExists('approve '.$permission);
      }

      $permissions = Permission::all();
      $this->role->syncPermissions($permissions);
  }

  protected function createSampleItem()
  {
      $item = new Item;
      $item->code = "Code001";
      $item->name = "Kopi Jowo";
      $item->chart_of_account_id = $this->account->id;
      $item->require_expiry_date = false;
      $item->require_production_number = false;
      $item->save();
      $this->item = $item;
  }

  protected function createSampleAllocation()
  {
      $allocation = new Allocation;
      $allocation->name = "Stok Pantry";
      $allocation->save();
      $this->allocation = $allocation;
  }

  private function createDataPurchaseRequest()
  {
      $data = [
          "increment_group" => date('Ym'),
          "date" => date('Y-m-d H:m:s'),
          "required_date" => date('Y-m-d H:m:s'),
          'employee_id' => $this->employee->id,
          "request_approval_to" => $this->user->id,
          "notes" => "Test Note",
          "items" => [
              [
                  "item_id" => $this->item->id,
                  "item_name" => $this->item->name,
                  "unit" => "PCS",
                  "converter" => "1.00",
                  "quantity" => "20",
                  "quantity_remaining" => "20",
                  "notes" => "notes",
                  "allocation_id" => $this->allocation->id,
              ]
          ]
      ];
      return $data;
  }

  protected function unsetUserRole()
  {    
    ModelHasRole::where('role_id', $this->role->id)
      ->where('model_type', 'App\Model\Master\User')
      ->where('model_id', $this->user->id)
      ->delete();
  }

  protected function setDefaultBranch($state = true)
  {
    $tenantUser = TenantUser::find($this->user->id);
    foreach ($tenantUser->branches as $branch) {
        $branch->pivot->is_default = $state;
        $branch->pivot->save();
    }
  }
}