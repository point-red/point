<?php

if (! function_exists('get_table_class')) {
    /**
     * Class name.
     *
     * @param $name
     * @return mixed
     */
    function get_table_class($name)
    {
        $class = [
            // Master
            'address' => \App\Model\Master\Address::class,
            'allocation' => \App\Model\Master\Allocation::class,
            'allocation_group' => \App\Model\Master\AllocationGroup::class,
            'bank' => \App\Model\Master\Bank::class,
            'branch' => \App\Model\Master\Branch::class,
            'contact_person' => \App\Model\Master\ContactPerson::class,
            'customer' => \App\Model\Master\Customer::class,
            'customer_group' => \App\Model\Master\CustomerGroup::class,
            'email' => \App\Model\Master\Email::class,
            'expedition' => \App\Model\Master\Expedition::class,
            'item' => \App\Model\Master\Item::class,
            'item_group' => \App\Model\Master\ItemGroup::class,
            'item_unit' => \App\Model\Master\ItemUnit::class,
            'phone' => \App\Model\Master\Phone::class,
            'price_list_item' => \App\Model\Master\PriceListItem::class,
            'price_list_service' => \App\Model\Master\PriceListService::class,
            'pricing_group' => \App\Model\Master\PricingGroup::class,
            'service' => \App\Model\Master\Service::class,
            'service_group' => \App\Model\Master\ServiceGroup::class,
            'supplier' => \App\Model\Master\Supplier::class,
            'supplier_group' => \App\Model\Master\SupplierGroup::class,
            'user' => \App\Model\Master\User::class,
            'warehouse' => \App\Model\Master\Warehouse::class,
            // Form
            'form' => \App\Model\Form::class,
            // Purchasing
            'purchase_request' => \App\Model\Purchase\PurchaseRequest\PurchaseRequest::class,
            'purchase_request_item' => \App\Model\Purchase\PurchaseRequest\PurchaseRequestItem::class,
        ];

        return $class[$name];
    }
}
