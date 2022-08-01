<?php

namespace App\Model\Sales\SalesReturn;

use App\Model\Form;
use App\Model\TransactionModel;

class SalesReturn extends TransactionModel
{
    public static $morphName = 'SalesReturn';

    protected $connection = 'tenant';

    public static $alias = 'sales_return';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the list of returned items.
     *
     * @return eloquent
     */
    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    /**
     * Get the list of returned items.
     *
     * @return eloquent
     */
    public function services()
    {
        return $this->hasMany(SalesReturnService::class);
    }

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public static function create($data)
    {
        $salesReturn = new self;
        $salesReturn->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesReturn->save();
        $salesReturn->items()->saveMany($items);
        $salesReturn->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $salesReturn);

        return $salesReturn;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $salesReturnItem = new SalesReturnItem;
            $salesReturnItem->fill($item);

            return $salesReturnItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $salesReturnService = new SalesReturnService;
            $salesReturnService->fill($service);

            return $salesReturnService;
        }, $services);
    }

    // TODO cancel, approval?
}
