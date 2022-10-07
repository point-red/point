<?php 

namespace Tests\Feature\Http\Sales\SalesReturn;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;

use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Warehouse;
use App\Model\Master\User as TenantUser;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryNote\DeliveryNoteItem;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\SettingJournal;
use App\Model\Accounting\Journal;

trait SalesReturnSetup {
  private $tenantUser;
  private $branchDefault;
  private $warehouseSelected;
  private $unit;
  private $item;
  private $customer;
  private $approver;
  private $coa;
  private $arCoa;
  private $salesIncomeCoa;
  private $salesCostCoa;
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

    $this->createCustomerUnitItem();
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

  private function createWarehouse($branch = null)
  {
      $warehouse = new Warehouse();
      $warehouse->name = 'Test warehouse';

      if($branch) $warehouse->branch_id = $branch->id;

      $warehouse->save();

      return $warehouse;
  }

  private function createCustomerUnitItem()
  {
      $this->customer = factory(Customer::class)->create();
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
        }

      $arCoaId = get_setting_journal('sales', 'account receivable');
      if ($arCoaId) {
          $arCoa = ChartOfAccount::where('id', $arCoaId)->first();
          $this->arCoa = $arCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'ACCOUNT RECEIVABLE';
          $type->alias = 'PIUTANG USAHA';
          $type->is_debit = 1;
          $type->save();

          $this->arCoa = new ChartOfAccount;
          $this->arCoa->type_id = $type->id;
          $this->arCoa->position = 'DEBIT';
          $this->arCoa->is_locked = 1;
          $this->arCoa->number = 11401;
          $this->arCoa->name = 'ACCOUNT RECEIVABLE';
          $this->arCoa->alias = 'PIUTANG DAGANG';
          $this->arCoa->created_by = $this->user->id;
          $this->arCoa->updated_by = $this->user->id;
          $this->arCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'sales';
          $setting->name = 'account receivable';
          $setting->chart_of_account_id = $this->arCoa->id;
          $setting->save();
      }

      $salesIncomeId = get_setting_journal('sales', 'sales income');
      if ($salesIncomeId) {
        $salesIncomeCoa = ChartOfAccount::where('id', $salesIncomeId)->first();
          $this->salesIncomeCoa = $salesIncomeCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'SALES INCOME';
          $type->alias = 'PENDAPATAN PENJUALAN';
          $type->is_debit = 0;
          $type->save();

          $this->salesIncomeCoa = new ChartOfAccount;
          $this->salesIncomeCoa->type_id = $type->id;
          $this->salesIncomeCoa->position = 'CREDIT';
          $this->salesIncomeCoa->is_locked = 1;
          $this->salesIncomeCoa->number = 41101;
          $this->salesIncomeCoa->name = 'SALES INCOME';
          $this->salesIncomeCoa->alias = 'PENJUALAN';
          $this->salesIncomeCoa->created_by = $this->user->id;
          $this->salesIncomeCoa->updated_by = $this->user->id;
          $this->salesIncomeCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'sales';
          $setting->name = 'sales income';
          $setting->chart_of_account_id = $this->salesIncomeCoa->id;
          $setting->save();
      }

      $salesCostCoaId = get_setting_journal('sales', 'cost of sales');
      if ($salesCostCoaId) {
          $salesCostCoa = ChartOfAccount::where('id', $salesCostCoaId)->first();
          $this->salesCostCoa = $salesCostCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'COST OF SALES';
          $type->alias = 'BEBAN POKOK PENJUALAN';
          $type->is_debit = 1;
          $type->save();

          $this->salesCostCoa = new ChartOfAccount;
          $this->salesCostCoa->type_id = $type->id;
          $this->salesCostCoa->position = 'DEBIT';
          $this->salesCostCoa->is_locked = 1;
          $this->salesCostCoa->number = 41200;
          $this->salesCostCoa->name = 'COST OF SALES';
          $this->salesCostCoa->alias = 'BEBAN POKOK PENJUALAN';
          $this->salesCostCoa->created_by = $this->user->id;
          $this->salesCostCoa->updated_by = $this->user->id;
          $this->salesCostCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'sales';
          $setting->name = 'cost of sales';
          $setting->chart_of_account_id = $this->salesCostCoa->id;
          $setting->save();
      }

      $taxCoaId = get_setting_journal('sales', 'income tax payable');
      if ($taxCoaId) {
          $taxCoa = ChartOfAccount::where('id', $taxCoaId)->first();
          $this->taxCoa = $taxCoa;
      } else {
          $type = new ChartOfAccountType;
          $type->name = 'OTHER ACCOUNT PAYABLE ';
          $type->alias = 'HUTANG EKSPEDISI';
          $type->is_debit = 0;
          $type->save();

          $this->taxCoa = new ChartOfAccount;
          $this->taxCoa->type_id = $type->id;
          $this->taxCoa->position = 'CREDIT';
          $this->taxCoa->is_locked = 1;
          $this->taxCoa->number = 21512;
          $this->taxCoa->name = 'INCOME TAX PAYABLE';
          $this->taxCoa->alias = 'PPH 23 YMH DIBAYAR';
          $this->taxCoa->created_by = $this->user->id;
          $this->taxCoa->updated_by = $this->user->id;
          $this->taxCoa->save();

          $setting = new SettingJournal;
          $setting->feature = 'sales';
          $setting->name = 'income tax payable';
          $setting->chart_of_account_id = $this->taxCoa->id;
          $setting->save();
      }
  }

  private function getDummyData($salesReturn = null)
    {
        $inv = $salesReturn ?? $this->createSalesInvoice();

        $invoice = $salesReturn ? $inv->salesInvoice : $inv;
        $invoiceItem = $invoice->items()->first();

        $quantityInvoice = $invoiceItem->quantity;
        $quantityReturned = $invoiceItem->quantity_returned;

        $customer = $invoice->customer;
        $approver = $invoice->form->requestApprovalTo;

        return [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'sales_invoice_id' => $invoice->id,
            "warehouse_id" => $this->warehouseSelected->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_label' => $customer->code,
            'customer_address' => null,
            'customer_phone' => null,
            'customer_email' => null,
            'notes' => null,
            'tax' => 3000,
            'amount' => 33000,
            'type_of_tax' => 'exclude',
            'items' => [
                [
                    'sales_invoice_item_id' => $invoiceItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => $invoiceItem->converter,
                    'quantity_sales' => $quantityInvoice,
                    'quantity' => 3,
                    'price' => $invoiceItem->price,
                    'total' => 3 * $invoiceItem->price,
                    'allocation_id' => null,
                    'notes' => null,
                ],
            ],
            'request_approval_to' => $approver->id,
            'approver_name' => $approver->name,
            'approver_email' => $approver->email,
        ];
    }

  private function createSalesInvoice()
  {

    $params =  [
        'increment_group' => date('Ym'),
        'date' => date('Y-m-d H:i:s'),
        'referenceable_id' => 1,
        'referenceable_type' => 'SalesDeliveryNote',
        'due_date' => date('Y-m-d H:i:s'),
        'customer_id' => $this->customer->id,
        'customer_name' => $this->customer->name,
        'customer_label' => $this->customer->code,
        'customer_address' => null,
        'customer_phone' => null,
        'customer_email' => null,
        'notes' => null,
        'delivery_fee' => 0,
        'discount_percent' => 0,
        'discount_value' => 0,
        'type_of_tax' => 'exclude',
        'tax' => 100000,
        'amount' => 1100000,
        'remaining' => 1100000,
        'items' => [
            [
                'referenceable_id' => 1,
                'referenceable_type' => 'SalesDeliveryNote',
                'item_referenceable_id' => 1,
                'item_referenceable_type' => 'SalesDeliveryNoteItem',
                'item_id' => $this->item->id,
                'item_name' => $this->item->name,
                'item_label' => "[{$this->item->code}] - {$this->item->name}",
                'more' => false,
                'unit' => $this->unit->label,
                'converter' => 1,
                'quantity' => 10,
                'quantity_returned' => 0,
                'require_expiry_date' => 0,
                'require_production_number' => 0,
                'price' => 10000,
                'discount_percent' => 0,
                'discount_value' => 0,
                'taxable' => 1,
                'total' => 100000,
                'allocation_id' => null,
                'notes' => null,
            ],
        ],
        'request_approval_to' => $this->approver->id,
        'approver_name' => $this->approver->name,
        'approver_email' => $this->approver->email,
    ];

    $salesInvoice = SalesInvoice::create($params);
    $salesInvoice->form->approval_by = $this->approver->id;
    $salesInvoice->form->approval_at = now();
    $salesInvoice->form->approval_status = 1;
    $salesInvoice->form->save();
    return $salesInvoice;
  }

  private function createPaymentCollection($salesReturn)
  {

    $params =  [
        'increment_group' => date('Ym'),
        'date' => date('Y-m-d H:i:s'),
        'payment_type' => 'cash',
        'due_date' => date('Y-m-d H:i:s'),
        'customer_id' => $this->customer->id,
        'customer_name' => $this->customer->name,
        'customer_label' => $this->customer->code,
        'customer_address' => null,
        'customer_phone' => null,
        'customer_email' => null,
        'notes' => null,
        'amount' => 30000,
        "details" => [
            [
                "date" => date("Y-m-d H:i:s"),
                "chart_of_account_id" => null,
                "chart_of_account_name" => null,
                "available" => $salesReturn->amount,
                "amount" => 30000,
                "allocation_id" => null,
                "allocation_name" => null,
                "referenceable_form_date" => $salesReturn->form->date,
                "referenceable_form_number" => $salesReturn->form->number,
                "referenceable_form_notes" => $salesReturn->form->notes,
                "referenceable_id" => $salesReturn->id,
                "referenceable_type" => "SalesReturn"
            ],
        ],
        'request_approval_to' => $this->approver->id,
        'approver_name' => $this->approver->name,
        'approver_email' => $this->approver->email,
    ];

    $paymentCollection = PaymentCollection::create($params);
    $paymentCollection->form->approval_by = $this->approver->id;
    $paymentCollection->form->approval_at = now();
    $paymentCollection->form->approval_status = 1;
    $paymentCollection->form->save();
    return $paymentCollection;
  }

}