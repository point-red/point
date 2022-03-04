<?php

namespace App\Providers;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAsset;
use App\Model\Accounting\CutOffDownPayment;
use App\Model\Accounting\CutOffInventory;
use App\Model\Accounting\CutOffPayment;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Inventory\InventoryAudit\InventoryAudit;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use App\Model\Inventory\OpeningStock\OpeningStock;
use App\Model\Inventory\StockCorrection\StockCorrection;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\Manufacture\ManufactureOutput\ManufactureOutput;
use App\Model\Master\Allocation;
use App\Model\Master\Branch;
use App\Model\Master\Customer;
use App\Model\Master\Expedition;
use App\Model\Master\FixedAsset;
use App\Model\Master\Item;
use App\Model\Master\Service;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Pos\PosBill;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use App\Model\Sales\SalesReturn\SalesReturn;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class PolymorphicTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Relation::morphMap([
            // Master
            Allocation::$morphName => Allocation::class,
            Supplier::$morphName => Supplier::class,
            Customer::$morphName => Customer::class,
            Employee::$morphName => Employee::class,
            Expedition::$morphName => Expedition::class,
            FixedAsset::$morphName => FixedAsset::class,
            Item::$morphName => Item::class,
            Service::$morphName => Service::class,
            Branch::$morphName => Branch::class,
            Warehouse::$morphName => Warehouse::class,
            // Inventory
            InventoryAudit::$morphName => InventoryAudit::class,
            OpeningStock::$morphName => OpeningStock::class,
            InventoryUsage::$morphName => InventoryUsage::class,
            StockCorrection::$morphName => StockCorrection::class,
            TransferItem::$morphName => TransferItem::class,
            ReceiveItem::$morphName => ReceiveItem::class,
            // Pos
            PosBill::$morphName => PosBill::class,
            // Purchase
            PurchaseRequest::$morphName => PurchaseRequest::class,
            PurchaseOrder::$morphName => PurchaseOrder::class,
            PurchaseReceive::$morphName => PurchaseReceive::class,
            PurchaseContract::$morphName => PurchaseContract::class,
            PurchaseDownPayment::$morphName => PurchaseDownPayment::class,
            PurchaseInvoice::$morphName => PurchaseInvoice::class,
            PurchaseReturn::$morphName => PurchaseReturn::class,
            // Sales
            SalesQuotation::$morphName => SalesQuotation::class,
            SalesOrder::$morphName => SalesOrder::class,
            SalesContract::$morphName => SalesContract::class,
            SalesDownPayment::$morphName => SalesDownPayment::class,
            DeliveryOrder::$morphName => DeliveryOrder::class,
            DeliveryNote::$morphName => DeliveryNote::class,
            SalesInvoice::$morphName => SalesInvoice::class,
            SalesReturn::$morphName => SalesReturn::class,
            // Manufacture
            ManufactureFormula::$morphName => ManufactureFormula::class,
            ManufactureInput::$morphName => ManufactureInput::class,
            ManufactureOutput::$morphName => ManufactureOutput::class,
            // Finance
            PaymentOrder::$morphName => PaymentOrder::class,
            Payment::$morphName => Payment::class,
            // Accounting
            ChartOfAccount::$morphName => ChartOfAccount::class,
            CutOff::$morphName => CutOff::class,
            CutOffPayment::$morphName => CutOffPayment::class,
            CutOffDownPayment::$morphName => CutOffDownPayment::class,
            CutOffAsset::$morphName => CutOffAsset::class,
            CutOffInventory::$morphName => CutOffInventory::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
