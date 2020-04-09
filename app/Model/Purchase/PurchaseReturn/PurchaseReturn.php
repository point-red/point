<?php

namespace App\Model\Purchase\PurchaseReturn;

use App\Model\TransactionModel;

class PurchaseReturn extends TransactionModel
{
    public static $morphName = 'PurchaseReturn';

    protected $connection = 'tenant';

    public static $alias = 'purchase_return';

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * Get the list of returned items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(PurchaseReturnService::class);
    }

    public static function create($data)
    {
        $purchaseReturn = new self;
        $purchaseReturn->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $purchaseReturn->save();
        $purchaseReturn->items()->saveMany($items);
        $purchaseReturn->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $purchaseReturn);

        return $purchaseReturn;
    }

    private static function mapItems($items)
    {
        return array_map(function ($item) {
            $purchaseReturnItem = new PurchaseReturnItem;
            $purchaseReturnItem->fill($item);

            return $purchaseReturnItem;
        }, $items);
    }

    private static function mapServices($services)
    {
        return array_map(function ($service) {
            $purchaseReturnService = new PurchaseReturnService;
            $purchaseReturnService->fill($service);

            return $purchaseReturnService;
        }, $services);
    }

    // TODO cancel, approval?
}
