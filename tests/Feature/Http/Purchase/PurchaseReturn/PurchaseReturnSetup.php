<?php 

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;

use App\Model\Master\Supplier;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;
use App\Model\SettingJournal;
use App\Model\Accounting\Journal;

use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;

trait PurchaseReturnSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;
  private $unit;
  private $item;
  private $supplier;
  private $approver;
  private $coa;
  private $apCoa;
  private $taxCoa;

  public function setUp(): void
  {
    parent::setUp();

    $this->signIn();
    $this->setProject();

    $this->tenantUser = TenantUser::find($this->user->id);
    $this->branchDefault = $this->tenantUser->branches()
            ->where('is_default', true)
            ->first();

    $this->createSupplierUnitItem();
    $this->setUserWarehouse($this->branchDefault);
    $this->setApprover();
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

  private function unsetBranch()
  {
    foreach ($this->tenantUser->branches as $branch) {
        $branch->pivot->is_default = false;
        $branch->pivot->save();
    }
  }

  private function setBranch()
  {
    foreach ($this->tenantUser->branches as $branch) {
        $branch->pivot->is_default = true;
        $branch->pivot->save();
    }
  }

  private function createWarehouse($branch = null)
  {
      $warehouse = new Warehouse();
      $warehouse->name = 'Test warehouse';

      if($branch) $warehouse->branch_id = $branch->id;

      $warehouse->save();

      return $warehouse;
  }

  private function createSupplierUnitItem()
  {
      $this->supplier = factory(Supplier::class)->create();
      $this->unit = factory(ItemUnit::class)->make();
      $this->item = factory(Item::class)->create();
      $this->item->units()->save($this->unit);

      $this->generateChartOfAccount();
      $this->item->chart_of_account_id = $this->coa->id;
      $this->item->save();
  }

  private function setApprover()
  {
      $role = Role::createIfNotExists('super admin');
      $this->approver = factory(TenantUser::class)->create();
      $this->approver->assignRole($role);
  }

  private function generateChartOfAccount()
  {
        $coa = ChartOfAccount::where('name', 'FINISHED GOOD INVENTORY')->first();
        if ($coa) {
            $this->coa = $coa;
        } else {
            $type = new ChartOfAccountType;
            $type->name = 'INVENTORY';
            $type->alias = 'PERSEDIAAN';
            $type->is_debit = 1;
            $type->save();

            $this->coa = new ChartOfAccount;
            $this->coa->type_id = $type->id;
            $this->coa->position = 'DEBIT';
            $this->coa->is_locked = 1;
            $this->coa->number = 11601;
            $this->coa->name = 'FINISHED GOOD INVENTORY';
            $this->coa->alias = 'PERSEDIAAN BARANG JADI ';
            $this->coa->created_by = $this->user->id;
            $this->coa->updated_by = $this->user->id;
            $this->coa->save();
            error_log('haha');
        }

      $apCoaJournal = SettingJournal::where('feature', 'purchase')->where('name', 'account payable')->first();
      if ($apCoaJournal) {
          $apCoa = ChartOfAccount::where('id', $apCoaJournal->chart_of_account_id)->first();
          $this->apCoa = $apCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'ACCOUNT PAYABLE';
          $type->alias = 'HUTANG USAHA';
          $type->is_debit = 0;
          $type->save();

          $this->apCoa = new ChartOfAccount;
          $this->apCoa->type_id = $type->id;
          $this->apCoa->position = 'CREDIT';
          $this->apCoa->is_locked = 1;
          $this->apCoa->number = 21201;
          $this->apCoa->name = 'ACCOUNT PAYABLE';
          $this->apCoa->alias = 'HUTANG DAGANG';
          $this->apCoa->created_by = $this->user->id;
          $this->apCoa->updated_by = $this->user->id;
          $this->apCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'purchase';
          $setting->name = 'account payable';
          $setting->chart_of_account_id = $this->apCoa->id;
          $setting->save();
      }

      $taxCoaJournal = SettingJournal::where('feature', 'purchase')->where('name', 'income tax receivable')->first();
      if ($taxCoaJournal) {
          $taxCoa = ChartOfAccount::where('id', $taxCoaJournal->chart_of_account_id)->first();
          $this->taxCoa = $taxCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'INCOME TAX RECEIVABLE';
          $type->alias = 'PPN MASUKAN';
          $type->is_debit = 1;
          $type->save();

          $this->taxCoa = new ChartOfAccount;
          $this->taxCoa->type_id = $type->id;
          $this->taxCoa->position = 'DEBIT';
          $this->taxCoa->is_locked = 1;
          $this->taxCoa->number = 11707;
          $this->taxCoa->name = 'INCOME TAX RECEIVABLE ';
          $this->taxCoa->alias = 'PPN MASUKAN';
          $this->taxCoa->created_by = $this->user->id;
          $this->taxCoa->updated_by = $this->user->id;
          $this->taxCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'purchase';
          $setting->name = 'income tax receivable';
          $setting->chart_of_account_id = $this->taxCoa->id;
          $setting->save();
      }
  }

  private function getDummyData($purchaseReturn = null)
  {
    $inv = $purchaseReturn ?? $this->createPurchaseInvoice();

    $invoice = $purchaseReturn ? $inv->purchaseInvoice : $inv;
    $invoiceItem = $invoice->items()->first();

    $quantityInvoice = $invoiceItem->quantity;
    $quantityReturned = 0;
    foreach ($invoiceItem->returnItem as $returnItem) {
    $quantityReturned += $returnItem->quantity;
    }

    $supplier = $invoice->supplier;
    $approver = $invoice->form->requestApprovalTo;

    return [
        'increment_group' => date('Ym'),
        'date' => date('Y-m-d H:i:s'),
        'purchase_invoice_id' => $invoice->id,
        "warehouse_id" => $invoiceItem->purchaseReceive->warehouse_id,
        'supplier_id' => $supplier->id,
        'supplier_name' => $supplier->name,
        'supplier_address' => null,
        'supplier_phone' => null,
        'tax' => 1500,
        'amount' => 15000,
        'notes' => null,
        'items' => [
            [
                'purchase_invoice_item_id' => $invoiceItem->id,
                'item_id' => $this->item->id,
                'item_name' => $this->item->name,
                'item_label' => "[{$this->item->code}] - {$this->item->name}",
                'unit' => $this->unit->label,
                'converter' => $invoiceItem->converter,
                'quantity' => 3,
                'price' => $invoiceItem->price,
                'discount_percent' => $invoiceItem->discount_percent,
                'discount_value' => $invoiceItem->discount_value,
                'notes' => null,
            ],
        ],
        'request_approval_to' => $approver->id,
        'approver_name' => $approver->name,
        'approver_email' => $approver->email,
    ];
  }

  private function createPurchaseOrder()
    {
        $params = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_label' => $this->supplier->code,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'supplier_email' => $this->supplier->phone,
            'need_down_payment' => 0,
            'cash_only' => false,
            'notes' => null,
            'discount_percent' => 0,
            'discount_value' => 0,
            'tax_base' => 1100000,
            'tax' => 110000,
            'type_of_tax' => 'exclude',
            'items' => [
                [
                    'purchase_request_item_id' => null,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'quantity' => '220',
                    'price' => 5000,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'allocation_id' => null,
                    'allocation_name' => '',
                    'notes' => null,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                    'units' => [
                        [
                            'id' => $this->unit->id,
                            'label' => $this->unit->label,
                            'name' => $this->unit->name,
                            'converter' => 1,
                            'disabled' => 0,
                            'item_id' => $this->item->id,
                            'created_by' => 1,
                            'updated_by' => 1,
                            'created_at' => '2022-05-13 10:38:42',
                            'updated_at' => '2022-05-13 10:38:42',
                            'prices' => [],
                        ],
                    ],
                ],
            ],
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'purchase_request_id' => null,
        ];

        $purchaseOrder = PurchaseOrder::create($params);
        $purchaseOrder->form->approval_by = $this->approver->id;
        $purchaseOrder->form->approval_at = now();
        $purchaseOrder->form->approval_status = 1;
        $purchaseOrder->form->save();

        return $purchaseOrder;
    }

    private function createPurchaseReceive()
    {
        $order = $this->createPurchaseOrder();
        $orderItem = $order->items()->first();

        $params = [
            'increment_group' => date('Ym'),
            'purchase_order_id' => $order->id,
            'date' => date('Y-m-d H:i:s'),
            'warehouse_id' => $this->warehouseSelected->id,
            'warehouse_name' => $this->warehouseSelected->name,
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_label' => $this->supplier->code,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'driver' => '-',
            'license_plate' => '-',
            'items' => [
                [
                    'id' => $orderItem->id,
                    'purchase_order_id' => $order->id,
                    'purchase_request_item_id' => null,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'quantity' => '220',
                    'price' => 5000,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'allocation_id' => null,
                    'notes' => null,
                    'purchase_order_item_id' => $orderItem->id,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                    'quantity_pending' => '220',
                    'warehouse_id' => $this->warehouseSelected->id,
                    'warehouse_name' => $this->warehouseSelected->name,
                ],
            ],
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'purchase_request_id' => null,
        ];

        $purchaseReceive = PurchaseReceive::create($params);
        $purchaseReceive->form->approval_by = $this->approver->id;
        $purchaseReceive->form->approval_at = now();
        $purchaseReceive->form->approval_status = 1;
        $purchaseReceive->form->save();

        return $purchaseReceive;
    }

    private function createPurchaseInvoice()
    {
        $receive = $this->createPurchaseReceive();
        $receiveItem = $receive->items()->first();

        $params = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s'),
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_label' => $this->supplier->code,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'supplier_email' => $this->supplier->phone,
            'need_down_payment' => 0,
            'cash_only' => false,
            'notes' => null,
            'discount_percent' => 0,
            'discount_value' => 0,
            'tax_base' => 1100000,
            'tax' => 110000,
            'type_of_tax' => 'exclude',
            'items' => [
                [
                    'purchase_receive_id' => $receive->id,
                    'purchase_receive_item_id' => $receiveItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'quantity' => '220',
                    'price' => 5000,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'allocation_id' => null,
                    'total' => 1100000,
                    'notes' => null,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                ],
            ],
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'purchase_request_id' => null,
        ];

        $purchaseInvoice = PurchaseInvoice::create($params);
        $purchaseInvoice->form->approval_by = $this->approver->id;
        $purchaseInvoice->form->approval_at = now();
        $purchaseInvoice->form->approval_status = 1;
        $purchaseInvoice->form->save();

        return $purchaseInvoice;
    }
}