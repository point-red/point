<?php 

namespace Tests\Feature\Http\Sales\DeliveryNote;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Auth\Role;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\SettingJournal;

trait DeliveryNoteSetup {
    private $tenantUser;
    private $branchDefault;
    private $warehouseSelected;
    private $unit;
    private $item;
    private $customer;
    private $approver;
    private $coa;
    
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', -1);

        $this->signIn();
        $this->setProject();

        $this->tenantUser = TenantUser::find($this->user->id);
        $this->branchDefault = $this->tenantUser->branches()
            ->where('is_default', true)
            ->first();

        $this->setUserWarehouse($this->branchDefault);
        $this->createCustomerUnitItem();
        $this->setApprover();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    private function setUserWarehouse()
    {
        $warehouse = $this->createWarehouse();
        $this->tenantUser->warehouses()->syncWithoutDetaching($warehouse->id);
        foreach ($this->tenantUser->warehouses as $warehouse) {
            $warehouse->pivot->is_default = true;
            $warehouse->pivot->save();

            $this->warehouseSelected = $warehouse;
        }
    }

    private function createWarehouse()
    {
        $warehouse = new Warehouse();
        $warehouse->name = 'Test warehouse';
        $warehouse->branch_id = $this->branchDefault->id;
        $warehouse->save();

        return $warehouse;
    }

    private function createCustomerUnitItem() {
        $this->customer = factory(Customer::class)->create();
        $this->unit = factory(ItemUnit::class)->make();
        $this->item = factory(Item::class)->create();
        $this->item->units()->save($this->unit);

        $this->generateChartOfAccount();
        $this->item->chart_of_account_id = $this->coa->id;
        $this->item->save();
    }

    private function setApprover() {
        $role = Role::createIfNotExists('super admin');
        $this->approver = factory(TenantUser::class)->create();
        $this->approver->assignRole($role);
    }

    private function setStock($quantity = 100)
    {
        $form             = new Form();
        $form->date       = date('Y-m-d H:i:s', time() - 3600);
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        InventoryHelper::increase($form, $this->warehouseSelected, $this->item, $quantity, $this->unit, 1);
    }

    private function generateChartOfAccount()
    {
        $coa = ChartOfAccount::where('name', 'COST OF SALES')->first();
        if ($coa) {
            $this->coa = $coa;
        } else {
        $type = new ChartOfAccountType;
        $type->name = 'COST OF SALES';
        $type->alias = 'BEBAN POKOK PENJUALAN';
        $type->is_debit = 1;
        $type->save();

        $this->coa = new ChartOfAccount;
        $this->coa->type_id = $type->id; 
        $this->coa->position = 'DEBIT';
        $this->coa->is_locked = 1;
        $this->coa->number = 41200;
        $this->coa->name = 'COST OF SALES';
        $this->coa->alias = 'BEBAN POKOK PENJUALAN';
        $this->coa->created_by = $this->user->id;
        $this->coa->updated_by = $this->user->id;
        $this->coa->save();

        $setting = new SettingJournal;
        $setting->feature = 'sales';
        $setting->name = 'cost of sales';
        $setting->chart_of_account_id = $this->coa->id;
        $setting->save();
        }
    }

    private function getDummyData()
    {
        $note = $this->createDeliveryOrder();
        $noteItem = $note->items()->first();

        $quantityDelivered = $noteItem->quantity_delivered;
        $quantityRemaining = $noteItem->quantity_delivered;

        return [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'delivery_order_id' => $note->id,
            'warehouse_id' => $this->warehouseSelected->id,
            'warehouse_name' => $this->warehouseSelected->name,
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_label' => $this->customer->code,
            'customer_address' => null,
            'customer_phone' => null,
            'customer_email' => null,
            'pricing_group_id' => 1,
            'need_down_payment' => 0,
            'cash_only' => false,
            'notes' => null,
            'discount_percent' => 0,
            'discount_value' => 0,
            'type_of_tax' => 'exclude',
            'items' => [
                [
                    'delivery_order_item_id' => $noteItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => $noteItem->converter,
                    'quantity' => $quantityDelivered,
                    'quantity_remaining' => $quantityRemaining,
                    'require_expiry_date' => 0,
                    'require_production_number' => 0,
                    'is_quantity_over' => false,
                    'price' => $noteItem->price,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'total' => $note->amount,
                    'allocation_id' => null,
                    'notes' => null,
                    'warehouse_id' => $this->warehouseSelected->id,
                    'warehouse_name' => $this->warehouseSelected->name,
                    'dna' => [
                        'quantity' => $quantityDelivered,
                        'production_number' => 'prod1',
                        'expiry_date' => '2022-05-13 10:38:42',
                        'stock' => $quantityRemaining,
                        'balance' => $quantityRemaining - $quantityDelivered,
                    ]
                ]
            ],
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->name,
            'approver_email' => $this->approver->email
        ];
    }

    private function createDeliveryOrder()
    {
        $order = $this->createSalesOrder();        
        $orderItem = $order->items()->first();

        $quantityRequested = $orderItem->quantity;
        $quantityDelivered = $orderItem->quantity;
        $quantityRemaining = $quantityRequested - $quantityDelivered;

        $params = [
            'increment_group' => date("Ym"),
            'date' => date("Y-m-d H:i:s"),
            'sales_order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_address' => $this->customer->address,
            'customer_phone' => $this->customer->phone,
            'warehouse_id' => $this->warehouseSelected->id,
            'pricing_group_id' => 1,
            'need_down_payment' => 0,
            'cash_only' => false,
            'notes' => null,
            'discount_percent' => 0,
            'discount_value' => 0,
            'type_of_tax' => 'exclude',
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'items' => [
                [
                    'sales_order_item_id' => $orderItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'item_label' => "[{$this->item->code}] - {$this->item->name}",
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'quantity_requested' => $quantityRequested,
                    'quantity_delivered' => $quantityDelivered,
                    'quantity_remaining' => $quantityRemaining,
                    'is_quantity_over' => false,
                    'price' => 1100,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'total' => 55000,
                    'allocation_id' => null,
                    'notes' => null,
                    'warehouse_id' => $this->warehouseSelected->id,
                    'warehouse_name' => $this->warehouseSelected->name
                ]
            ],
        ];

        $deliveryOrder = DeliveryOrder::create($params);
        $deliveryOrder->form->approval_by = $this->approver->id;
        $deliveryOrder->form->approval_at = now();
        $deliveryOrder->form->approval_status = 1;
        $deliveryOrder->form->save();

        return $deliveryOrder;
    }

    private function createSalesOrder()
    {
        $params = [
            'increment_group' => date('Ym'),
            'date' => date('Y-m-d H:i:s'),
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_label' => $this->customer->code,
            'customer_address' => $this->customer->address,
            'customer_phone' => $this->customer->phone,
            'pricing_group_id' => 1,
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
                    'sales_quotation_item_id' => null,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'quantity' => "220",
                    'price' => 5000,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'allocation_id' => null,
                    'allocation_name' => "",
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
                            'prices' => []
                        ]
                    ]
                ]
            ],
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'sales_quotation_id' => null,
            'subtotal' => 1100000,
            'total' => 1210000
        ];

        $salesOrder = SalesOrder::create($params);
        $salesOrder->form->approval_by = $this->approver->id;
        $salesOrder->form->approval_at = now();
        $salesOrder->form->approval_status = 1;
        $salesOrder->form->save();

        return $salesOrder;
    }
}
