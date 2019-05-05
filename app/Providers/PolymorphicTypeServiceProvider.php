<?php

namespace App\Providers;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseContract\PurchaseContract;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use App\Model\Sales\SalesContract\SalesContract;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesOrder\SalesOrder;
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
            'Supplier' => Supplier::class,
            'Customer' => Customer::class,
            'Employee' => Employee::class,
            'PurchaseOrder' => PurchaseOrder::class,
            'PurchaseContract' => PurchaseContract::class,
            'PurchaseDownPayment' => PurchaseDownPayment::class,
            'PurchaseInvoice' => PurchaseInvoice::class,
            'PurchaseReturn' => PurchaseReturn::class,
            'SalesOrder' => SalesOrder::class,
            'SalesContract' => SalesContract::class,
            'SalesDownPayment' => SalesDownPayment::class,
            'SalesInvoice' => SalesInvoice::class,
            'SalesReturn' => SalesReturn::class,
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
