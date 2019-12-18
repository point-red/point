<?php

namespace App\Providers;

use App\Model\Accounting\CutOff;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Inventory\InventoryAudit\InventoryAudit;
use App\Model\Inventory\OpeningStock\OpeningStock;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Master\Service;
use App\Model\Master\Supplier;
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
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use App\Model\Manufacture\ManufactureOutput\ManufactureOutput;
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
            Supplier::$morphName => Supplier::class,
            Customer::$morphName => Customer::class,
            Employee::$morphName => Employee::class,
            Item::$morphName => Item::class,
            Service::$morphName => Service::class,
            // Inventory
            InventoryAudit::$morphName => InventoryAudit::class,
            OpeningStock::$morphName => OpeningStock::class,
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
            CutOff::$morphName => CutOff::class,
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
