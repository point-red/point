<?php 

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;

use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;

use App\Model\Sales\SalesOrder\SalesOrder;

trait DeliveryOrderSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;

  public function setUp(): void
  {
    parent::setUp();

    $this->signIn();
    $this->setProject();

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

  private function createSalesOrderApprove()
  {
    $customer = factory(Customer::class)->create();

    $unit = factory(ItemUnit::class)->make();
    $item = factory(Item::class)->create();
    $item->units()->save($unit);

    $role = Role::createIfNotExists('super admin');
    $approver = factory(TenantUser::class)->create();
    $approver->assignRole($role);

    $params = [
      "increment_group" => date("Ym"),
      "date" => date("Y-m-d H:i:s"),
      "customer_id" => $customer->id,
      "customer_name" => $customer->name,
      "customer_label" => $customer->code,
      "customer_address" => $customer->address,
      "customer_phone" => $customer->phone,
      "pricing_group_id" => 1,
      "need_down_payment" => 0,
      "cash_only" => false,
      "notes" => null,
      "discount_percent" => 0,
      "discount_value" => 0,
      "tax_base" => 1100000,
      "tax" => 110000,
      "type_of_tax" => "exclude",
      "items" => [
        [
          "sales_quotation_item_id" => null,
          "item_id" => $item->id,
          "item_name" => $item->name,
          "more" => false,
          "unit" => $unit->label,
          "converter" => 1,
          "quantity" => "220",
          "price" => 5000,
          "discount_percent" => 0,
          "discount_value" => 0,
          "allocation_id" => null,
          "allocation_name" => "",
          "notes" => null,
          "item_label" => "[{$item->code}] - {$item->name}",
          "units" => [
              [
                  "id" => $unit->id,
                  "label" => $unit->label,
                  "name" => $unit->name,
                  "converter" => 1,
                  "disabled" => 0,
                  "item_id" => $item->id,
                  "created_by" => 1,
                  "updated_by" => 1,
                  "created_at" => "2022-05-13 10:38:42",
                  "updated_at" => "2022-05-13 10:38:42",
                  "prices" => []
              ]
          ]
        ]
      ],
      "request_approval_to" => $approver->id,
      "approver_name" => $approver->getFullNameAttribute(),
      "approver_email" => $approver->email,
      "sales_quotation_id" => null,
      "subtotal" => 1100000,
      "total" => 1210000
    ];

    $salesOrder = SalesOrder::create($params);
    $salesOrder->form->approval_by = $approver->id;
    $salesOrder->form->approval_at = now();
    $salesOrder->form->approval_status = 1;
    $salesOrder->form->save();
    
    return $salesOrder;
  }
  
  private function getDummyData($deliveryOrder = null)
  {
    $warehouse = $this->warehouseSelected;
    $order = $deliveryOrder ?? $this->createSalesOrderApprove();
    
    $customer = $order->customer;
    $approver = $order->form->requestApprovalTo;
    
    $orderItem = $order->items()->first();
    $item = $orderItem->item;

    $salesOrder = $deliveryOrder ? $order->salesOrder : $order;
    $salesOrderItemId = $deliveryOrder ? $orderItem->sales_order_item_id : $orderItem->id;

    $quantityRequested = $deliveryOrder ? $orderItem->quantity_requested : $orderItem->quantity;
    $quantityDelivered = $deliveryOrder ? $orderItem->quantity_delivered : $orderItem->quantity;
    $quantityRemaining = $deliveryOrder ? $orderItem->quantity_remaining : $quantityRequested - $quantityDelivered;

    return [
      "increment_group" => date("Ym"),
      "date" => date("Y-m-d H:i:s"),
      "warehouse_id" => $warehouse->id,
      "warehouse_name" => $warehouse->name,
      "customer_id" => $customer->id,
      "customer_name" => $customer->name,
      "customer_label" => $customer->code,
      "customer_address" => null,
      "customer_phone" => null,
      "customer_email" => null,
      "pricing_group_id" => 1,
      "need_down_payment" => 0,
      "cash_only" => false,
      "notes" => null,
      "discount_percent" => 0,
      "discount_value" => 0,
      "type_of_tax" => "exclude",
      "items" => [
        [
          "sales_order_item_id" => $salesOrderItemId,
          "item_id" => $orderItem->item_id,
          "item_name" => $orderItem->item_name,
          "item_label" => "[{$item->code}] - {$item->name}",
          "more" => false,
          "unit" => $orderItem->unit,
          "converter" => $orderItem->converter,
          "quantity_requested" => $quantityRequested,
          "quantity_delivered" => $quantityDelivered,
          "quantity_remaining" => $quantityRemaining,
          "is_quantity_over" => false,
          "price" => $orderItem->price,
          "discount_percent" => 0,
          "discount_value" => 0,
          "total" => $order->amount,
          "allocation_id" => null,
          "notes" => null,
          "warehouse_id" => $warehouse->id,
          "warehouse_name" => $warehouse->name
        ]
      ],
      "request_approval_to" => $approver->id,
      "approver_name" => $approver->name,
      "approver_email" => $approver->email,
      "sales_order_id" => $salesOrder->id
    ];
  }
}