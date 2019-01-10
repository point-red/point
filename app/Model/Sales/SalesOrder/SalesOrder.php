<?php

namespace App\Model\Sales\SalesOrder;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Warehouse;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'sales_quotation_id',
        'sales_contract_id',
        'customer_id',
        'warehouse_id',
        'eta',
        'cash_only',
        'need_down_payment',
        'delivery_fee',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
    ];

    protected $casts = [
        'delivery_fee'  => 'double',
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function services()
    {
        return $this->hasMany(SalesOrderService::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function create($data)
    {
        $salesOrder = new self;
        $salesOrder->fill($data);
        $salesOrder->save();

        $form = new Form;
        $form->fill($data);
        $form->formable_id = $salesOrder->id;
        $form->formable_type = self::class;
        $form->generateFormNumber(
            isset($data['number']) ? $data['number'] : 'SO{y}{m}{increment=4}',
            null,
            isset($data['customer_id']) ? $data['customer_id'] : null
        );
        $form->save();

        $array = [];
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $salesOrderItem = new SalesOrderItem;
            $salesOrderItem->fill($item);
            $salesOrderItem->sales_order_id = $salesOrder->id;
            array_push($array, $salesOrderItem);
        }
        $salesOrder->items()->saveMany($array);

        $array = [];
        $services = $data['services'] ?? [];
        foreach ($services as $service) {
            $salesOrderService = new SalesOrderService;
            $salesOrderService->fill($service);
            $salesOrderService->sales_order_id = $salesOrder->id;
            array_push($array, $salesOrderService);
        }
        $salesOrder->services()->saveMany($array);

        $salesOrder->form();

        return $salesOrder;
    }
}
