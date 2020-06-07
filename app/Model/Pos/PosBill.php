<?php

namespace App\Model\Pos;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Form;
use App\Model\TransactionModel;
use App\Traits\Model\Pos\PosBillJoin;
use App\Traits\Model\Pos\PosBillRelation;

class PosBill extends TransactionModel
{
    use PosBillJoin, PosBillRelation;

    public static $morphName = 'PosBill';

    protected $connection = 'tenant';

    public static $alias = 'pos_bill';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'discount_percent',
        'discount_value',
        'type_of_tax',
        'tax',
        'paid',
        'notes',
        'warehouse_id',
    ];

    protected $casts = [
        'discount_percent' => 'double',
        'discount_value' => 'double',
        'tax' => 'double',
        'amount' => 'double',
        'paid' => 'double',
    ];

    public $defaultNumberPrefix = 'BI';

    public function isAllowedToUpdate()
    {
        $this->updatedFormNotArchived();
    }

    public function isAllowedToDelete()
    {
        $this->updatedFormNotArchived();
    }

    public static function create($data)
    {
        $bill = new self;
        $bill->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $bill->amount = self::calculateAmount($bill, $items, $services);
        $bill->save();

        $bill->items()->saveMany($items);
        $bill->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $bill);

        $bill->form()->update(['done' => $data['is_done']]);

        if ($data['is_done']) {
            foreach ($items as $item) {
                $options = [];
                if ($item->expiry_date) {
                    $options['expiry_date'] = $item->expiry_date;
                }
                if ($item->production_number) {
                    $options['production_number'] = $item->production_number;
                }
                InventoryHelper::decrease($form, $bill->warehouse, $item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }

        return $bill;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $billItem = new PosBillItem;
            $billItem->fill($item);

            return $billItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $billService = new PosBillService;
            $billService->fill($service);

            return $billService;
        }, $services);
    }

    private static function calculateAmount($bill, $items, $services)
    {
        $amount = array_reduce($items, function ($carry, $item) {
            return $carry + $item->quantity * ($item->price - $item->discount_value);
        }, 0);

        $amount += array_reduce($services, function ($carry, $service) {
            return $carry + $service->quantity * ($service->price - $service->discount_value);
        }, 0);

        $amount -= $bill->discount_value;
        $amount += $bill->type_of_tax === 'exclude' ? $bill->tax : 0;

        return $amount;
    }
}
