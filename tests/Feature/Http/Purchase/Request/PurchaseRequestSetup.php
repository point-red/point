<?php 

namespace Tests\Feature\Http\Purchase\Request;

use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;

use App\Model\Master\Item;
use App\Model\Auth\Permission;
use App\Model\Master\Allocation;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Supplier;
use DateTime;
use DateTimeZone;
use Faker\Factory;

trait PurchaseRequestSetup {

  public static $path = '/api/v1/purchase/requests';
  private $tenantUser;
  private $branchDefault;
  protected $item;
  protected $allocation;
  protected $purchase;

  public function setUp(): void
  {
      parent::setUp();
      $this->setupUser();
      $this->setProject();
      $this->createSampleChartAccountType();
      $this->createSampleEmployee();
      $this->createSampleItem();
      $this->createSampleAllocation();
  }

  public function setupUser($customRole = false, $setupPermission = true)
  {
    $this->signIn();
    if($customRole){
        $this->setCustomRole();
    }else{
        $this->setRole();
    }
    if($setupPermission){
        $this->setPurchaseRequestPermission();
    }
    $this->tenantUser = TenantUser::find($this->user->id);
  }

  protected function unsetBranch()
  {
    foreach ($this->tenantUser->branches as $branch) {
        $this->tenantUser->branches()->detach($branch->pivot->branch_id);
    }
  }

  protected function setPurchaseRequestPermission()
  {
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

  protected function setCustomRole()
    {
        $faker = Factory::create();
        $role = \App\Model\Auth\Role::createIfNotExists($faker->name);
        $hasRole = new \App\Model\Auth\ModelHasRole();
        $hasRole->role_id = $role->id;
        $hasRole->model_type = 'App\Model\Master\User';
        $hasRole->model_id = $this->user->id;
        $hasRole->save();
        $this->role = $role;
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

  private function createSupplier()
  {
    factory(Supplier::class, 1)->create();
    return Supplier::take(1)->first();
  }

  private function createPurchaseOrder($purchaseRequest)
  {
      $supplier = $this->createSupplier();
      $data = [
          "increment_group" => date('Ym'),
          "date" => date('Y-m-d H:m:s'),
          'supplier_id' => $supplier->id,
          'supplier_name' => $supplier->name,
          'purchase_request_id' => $purchaseRequest->id,
          "request_approval_to" => $this->user->id,
          "tax" => 95000,
          "tax_base" => 950000,
          "total" => 1045000,
          "discount_percent" => 0,
          "discount_value" => 0,
          "type_of_tax" => "exclude",
          "need_down_payment" => 0,
          "cash_only" => false,
          "notes" => "Test Note",
          "items" => [
              [
                  "purchase_request_item_id" => $purchaseRequest->items[0]->id,
                  "item_id" => $this->item->id,
                  "item_name" => $this->item->name,
                  "unit" => "PCS",
                  "converter" => "1.00",
                  "quantity" => "20",
                  "discount_percent" => 0,
                  "discount_value" => 5000,
                  "price" => 1000000,
                  "notes" => "notes",
                  "allocation_id" => $this->allocation->id,
              ]
          ]
      ];
      $response = $this->json('POST', '/api/v1/purchase/orders', $data, $this->headers);

      // save data
      $result = json_decode($response->getContent())->data;
      var_dump($result);
      
      return $result;
  }

  protected function convertDateTime($date, $format = 'Y-m-d H:i:s')
  {
    $tz1 = 'Asia/Jakarta';
    $tz2 = 'UTC';

    $d = new DateTime($date, new DateTimeZone($tz1));
    $d->setTimeZone(new DateTimeZone($tz2));

    return $d->format($format);
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
    foreach ($this->tenantUser->branches as $branch) {
        $branch->pivot->is_default = $state;
        $branch->pivot->save();
    }
  }
}