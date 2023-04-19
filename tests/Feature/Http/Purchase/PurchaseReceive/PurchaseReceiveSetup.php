<?php
namespace Tests\Feature\Http\Purchase\PurchaseReceive;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Auth\ModelHasRole;
use App\Model\Auth\Role;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\Supplier;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\SettingJournal;

trait PurchaseReceiveSetup
{
    private $tenantUser;
    private $branchDefault;
    private $warehouseSelected;
    private $unit;
    private $item;
    private $customer;
    private $supplier;
    private $approver;
    private $coa;

    public static $path = '/api/v1/purchase/receives';

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

    protected function unsetUserRole()
    {
        $role = Role::createIfNotExists('super admin');

        ModelHasRole::where('role_id', $role->id)
            ->where('model_type', 'App\Model\Master\User')
            ->where('model_id', $this->user->id)
            ->delete();
    }

    protected function unsetDefaultBranch()
    {
        $this->branchDefault->pivot->is_default = false;
        $this->branchDefault->save();

        $this->tenantUser->branches()->detach($this->branchDefault->pivot->branch_id);
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

    private function createWarehouse($branch = null)
    {
        $warehouse = new Warehouse();
        $warehouse->name = 'Test warehouse';
        if ($branch) {
            $warehouse->branch_id = $branch->id;
        }
        $warehouse->save();

        return $warehouse;
    }

    private function createCustomerUnitItem()
    {
        $this->customer = factory(Customer::class)->create();
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

    private function setStock($quantity = 100)
    {
        $form = new Form();
        $form->date = date('Y-m-d H:i:s', time() - 3600);
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

    private function getDummyData($purchaseReceive = null)
    {
        $purchase = $purchaseReceive->purchaseOrder ?? $this->createPurchaseOrder();
        $purchaseItem = $purchase->items()->first();

        $quantityDelivered = $purchase->quantity_delivered;
        $quantityRemaining = $purchase->quantity_delivered;

        return [
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'warehouse_id' => $this->warehouseSelected->id,
            'warehouse_name' => $this->warehouseSelected->name,
            'purchase_order_id' => $purchase->id,
            'driver' => '',
            'license_plate' => '',
            'date' => now(),
            'increment_group' => 1,
            'items' => [
                [
                    'purchase_order_item_id' => $purchaseItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'more' => false,
                    'unit' => $this->unit->label,
                    'converter' => $purchaseItem->converter,
                    'quantity' => 10,
                    'quantity_remaining' => $quantityRemaining,
                    'require_expiry_date' => 0,
                    'require_production_number' => 0,
                    'is_quantity_over' => false,
                    'price' => $purchaseItem->price,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'total' => $purchase->amount,
                    'allocation_id' => null,
                    'notes' => null,
                    'warehouse_id' => $this->warehouseSelected->id,
                    'warehouse_name' => $this->warehouseSelected->name,
                    'dna' => [
                        [
                            'quantity' => 10,
                            'production_number' => 'prod1',
                            'expiry_date' => '2022-05-13 10:38:42',
                            'stock' => $quantityRemaining,
                            'balance' => $quantityRemaining - $quantityDelivered
                        ]
                    ],
                ],
            ],
        ];
    }

    private function createPurchaseOrder()
    {
        $purchaseRequest = $this->createPurchaseRequest();
        $orderItem = $purchaseRequest->items()->first();

        $params = [
            'purchase_request_id' => $purchaseRequest->id,
            'purchase_contract_id' => null,
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'warehouse_id' => $this->warehouseSelected->id,
            'request_approval_to' => $this->approver->id,
            'approver_name' => $this->approver->getFullNameAttribute(),
            'approver_email' => $this->approver->email,
            'items' => [
                [
                    'purchase_request_item_id' => $orderItem->id,
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'quantity' => 10,
                    'price' => 1100,
                    'discount_percent' => 0,
                    'discount_value' => 0,
                    'taxable' => false,
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'allocation_id' => null,
                    'notes' => null
                ],
            ],
        ];

        $deliveryOrder = PurchaseOrder::create($params);
        $deliveryOrder->form->approval_by = $this->approver->id;
        $deliveryOrder->form->approval_at = now();
        $deliveryOrder->form->approval_status = 1;
        $deliveryOrder->form->save();

        return $deliveryOrder;
    }

    private function createPurchaseRequest()
    {
        $params = [
            'required_date' => date('Y-m-d H:i:s'),
            'supplier_id' => $this->supplier->id,
            'supplier_name' => $this->supplier->name,
            'supplier_address' => $this->supplier->address,
            'supplier_phone' => $this->supplier->phone,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'quantity' => '220',
                    'unit' => $this->unit->label,
                    'converter' => 1,
                    'price' => 5000,
                    'notes' => null,
                    'allocation_id' => null,
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
            'amount' => 1210000
        ];

        $purchaseRequest = PurchaseRequest::create($params);
        $purchaseRequest->form->approval_by = $this->approver->id;
        $purchaseRequest->form->approval_at = now();
        $purchaseRequest->form->approval_status = 1;
        $purchaseRequest->form->save();

        return $purchaseRequest;
    }
}