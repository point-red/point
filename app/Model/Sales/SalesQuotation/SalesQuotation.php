<?php

namespace App\Model\Sales\SalesQuotation;

use App\Exceptions\IsReferencedException;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\SalesOrder\SalesOrder;
use App\Model\TransactionModel;

class SalesQuotation extends TransactionModel
{
    public static $morphName = 'SalesQuotation';

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public $defaultNumberPrefix = 'SQ';

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesQuotationService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'sales_quotation_id')
            ->join('forms', 'forms.id', '=', 'sales_orders.form_id')
            ->where('forms.canceled', false)
            ->orWhereNull('forms.canceled');
    }

    public function updateIfDone()
    {
        // TODO check service too
        $done = true;
        $items = $this->items()->with('salesOrderItems')->get();
        foreach ($items as $item) {
            $quantitySent = $item->salesOrderItems->sum('quantity');
            if ($item->quantity > $quantitySent) {
                $done = false;
                break;
            }
        }

        $this->form()->update(['done' => $done]);
    }

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by purchase order
        if ($this->salesOrders->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales order(s)', $this->salesOrders);
        }
    }

    public static function create($data)
    {
        $salesQuotation = new self;
        $salesQuotation->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesQuotation->amount = self::calculateAmount($salesQuotation, $items, $services);
        $salesQuotation->save();

        $salesQuotation->items()->saveMany($items);
        $salesQuotation->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $salesQuotation);

        return $salesQuotation;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $salesQuotationItem = new SalesQuotationItem;
            $salesQuotationItem->fill($item);

            return $salesQuotationItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $salesQuotationService = new SalesQuotationService;
            $salesQuotationService->fill($service);

            return $salesQuotationService;
        }, $services);
    }

    private static function calculateAmount($items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + ($item->price - $item->discount_value) * $item->quantity * $item->converter;
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + ($service->price - $service->discount_value) * $service->quantity;
        }, 0);

        return $amount;
    }
}
