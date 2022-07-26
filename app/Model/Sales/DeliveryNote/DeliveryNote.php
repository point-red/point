<?php

namespace App\Model\Sales\DeliveryNote;

use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\DeliveryNoteJoin;
use App\Traits\Model\Sales\DeliveryNoteRelation;

class DeliveryNote extends TransactionModel
{
    use DeliveryNoteJoin, DeliveryNoteRelation;

    public static $morphName = 'SalesDeliveryNote';

    protected $connection = 'tenant';

    public static $alias = 'sales_delivery_note';

    protected $table = 'delivery_notes';

    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'delivery_order_id',
        'driver',
        'license_plate',
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'billing_address',
        'billing_phone',
        'billing_email',
        'shipping_address',
        'shipping_phone',
        'shipping_email',
    ];

    public $defaultNumberPrefix = 'DN';

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
        // Check if not referenced by sales invoice
        if ($this->salesInvoices->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by sales invoice(s)', $this->salesInvoices);
        }
    }

    public static function create($data)
    {
        $deliveryNote = new self;
        $deliveryNote->fill($data);

        $deliveryOrder = DeliveryOrder::findOrFail($data['delivery_order_id']);
        // TODO add check if $deliveryOrder is canceled / rejected / archived

        $deliveryNote->customer_id = $deliveryOrder->customer_id;
        $deliveryNote->customer_name = $deliveryOrder->customer_name;
        $deliveryNote->billing_address = $deliveryOrder->billing_address;
        $deliveryNote->billing_phone = $deliveryOrder->billing_phone;
        $deliveryNote->billing_email = $deliveryOrder->billing_email;
        $deliveryNote->shipping_address = $deliveryOrder->shipping_address;
        $deliveryNote->shipping_phone = $deliveryOrder->shipping_phone;
        $deliveryNote->shipping_email = $deliveryOrder->shipping_email;

        $deliveryNote->save();

        $items = self::mapItems($data['items'] ?? [], $deliveryOrder);

        $deliveryNote->items()->saveMany($items);

        $form = new Form;
        $form->saveData($data, $deliveryNote);

        $deliveryOrder->updateStatus();

        foreach ($items as $item) {
            $options = [];
            if ($item->expiry_date) {
                $options['expiry_date'] = $item->expiry_date;
            }
            if ($item->production_number) {
                $options['production_number'] = $item->production_number;
            }

            $options['quantity_reference'] = $item->quantity;
            $options['unit_reference'] = $item->unit;
            $options['converter_reference'] = $item->converter;
            InventoryHelper::decrease($form, $deliveryNote->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
        }

        self::updateJournal($deliveryNote);

        return $deliveryNote;
    }

    private static function mapItems($items, $deliveryOrder)
    {
        $deliveryOrderItems = $deliveryOrder->items;

        $array = [];
        foreach ($items as $item) {
            $itemModel = Item::find($item['item_id']);
            if ($itemModel->require_production_number || $itemModel->require_expiry_date) {
                if (isset($item['dna'])) {
                    foreach ($item['dna'] as $dna) {
                        if ($dna['quantity'] > 0) {
                            $dnaItem = $item;
                            $dnaItem['quantity'] = $dna['quantity'];
                            $dnaItem['production_number'] = $dna['production_number'];
                            $dnaItem['expiry_date'] = $dna['expiry_date'];
                            $dnaItem['stock'] = $dna['remaining'];
                            $dnaItem['balance'] = $dna['remaining'] - $dna['quantity'];

                            unset($dnaItem['dna']);
                            array_push($array, $dnaItem);
                        }
                    }
                }
            } else {
                array_push($array, $item);
            }
        }

        return array_map(function ($item) use ($deliveryOrderItems) {
            $deliveryOrderItem = $deliveryOrderItems->firstWhere('id', $item['delivery_order_item_id']);

            $deliveryNoteItem = new DeliveryNoteItem;
            $deliveryNoteItem->fill($item);
            $deliveryNoteItem = self::setDeliveryNoteItem($deliveryNoteItem, $deliveryOrderItem);

            return $deliveryNoteItem;
        }, $array);
    }

    private static function setDeliveryNoteItem($deliveryNoteItem, $deliveryOrderItem)
    {
        $deliveryNoteItem->item_id = $deliveryOrderItem->item_id;
        $deliveryNoteItem->item_name = $deliveryOrderItem->item_name;
        $deliveryNoteItem->price = $deliveryOrderItem->price;
        $deliveryNoteItem->discount_percent = $deliveryOrderItem->discount_percent;
        $deliveryNoteItem->discount_value = $deliveryOrderItem->discount_value;
        $deliveryNoteItem->taxable = $deliveryOrderItem->taxable;
        $deliveryNoteItem->allocation_id = $deliveryOrderItem->allocation_id;

        return $deliveryNoteItem;
    }

    public static function updateJournal($deliveryNote)
    {
        $amounts = 0;
        $journal = new Journal;
        $journal->form_id = $deliveryNote->form->id;
        $journal->journalable_type = Item::$morphName;
        $journal->journalable_id = $deliveryNote->delivery_order_id;
        $journal->chart_of_account_id = get_setting_journal('sales', 'cost of sales');
        $journal->debit = $amounts;
        $journal->save();

        foreach ($deliveryNote->items as $item) {
            $amount = $item->item->cogs($item->item_id) * $item->quantity;
            $amounts += $amount;

            $journalCredit = new Journal;
            $journalCredit->form_id = $deliveryNote->form->id;
            $journalCredit->journalable_type = Item::$morphName;
            $journalCredit->journalable_id = $item->item_id;
            $journalCredit->chart_of_account_id = $item->item->chart_of_account_id;
            $journalCredit->credit = $amount;
            $journalCredit->save();
        }

        $journal->debit = $amounts;
        $journal->save();
    }
}
