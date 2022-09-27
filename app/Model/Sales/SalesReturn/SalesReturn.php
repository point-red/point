<?php

namespace App\Model\Sales\SalesReturn;

use App\Model\Form;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Master\Item;
use App\Traits\Model\Sales\SalesReturnRelation;
use App\Traits\Model\Sales\SalesReturnJoin;
use Exception;
use App\Helpers\Inventory\InventoryHelper;
use App\Exceptions\IsReferencedException;

class SalesReturn extends TransactionModel
{
    use SalesReturnJoin, SalesReturnRelation;
    public static $morphName = 'SalesReturn';

    protected $connection = 'tenant';

    public static $alias = 'sales_return';

    public $defaultNumberPrefix = 'SR';

    protected $fillable = [
        'sales_invoice_id',
        'customer_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'tax',
        'amount',
        'warehouse_id'
    ];

    protected $casts = [
        'tax' => 'double',
        'amount' => 'double',
    ];

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

    public static function create($data)
    {
        $salesReturn = new self;
        $salesReturn->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesReturn->checkQuantity($items);

        $salesReturn->save();
        $salesReturn->items()->saveMany($items);
        //$salesReturn->services()->saveMany($services);

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

    public function checkQuantity($requestSalesReturnItems)
    {
        foreach ($this->salesInvoice->items as $salesInvoiceItem) {
            $salesInvoiceItem->convertUnitToSmallest();
            foreach ($requestSalesReturnItems as $item) {
                $returnedItemQuantity = $salesInvoiceItem->salesReturnItemReturned();
                
                if ($salesInvoiceItem->quantity < ($item->quantity + $returnedItemQuantity)) {
                    throw new Exception("Sales return item can't exceed sales invoice qty", 422);
                }
            }
        }
    }
    
    public static function updateInvoiceQuantity($salesReturn, $type)
    {
        foreach ($salesReturn->salesInvoice->items as $salesInvoiceItem) {
            foreach ($salesReturn->items as $item) {
                if ($salesInvoiceItem->id === $item->sales_invoice_item_id) {
                    $quantity = $item->quantity;
                    if ($type === 'revert') {
                        $quantity = $item->quantity * -1;
                    }
                    $salesInvoiceItem->quantity_returned 
                        = $salesInvoiceItem->quantity_returned + $quantity;
                    $salesInvoiceItem->quantity_remaining
                        = $salesInvoiceItem->quantity - $salesInvoiceItem->quantity_returned;
                    $salesInvoiceItem->save();
                }
            }
        }
    }

    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $transferItem
     */
    public static function updateInventory($form, $salesReturn)
    {
        foreach ($salesReturn->items as $item) {
            if ($item->quantity > 0) {
                $options = [];
                if ($item->item->require_expiry_date) {
                    $options['expiry_date'] = $item->expiry_date;
                }
                if ($item->item->require_production_number) {
                    $options['production_number'] = $item->production_number;
                }

                $options['quantity_reference'] = $item->quantity;
                $options['unit_reference'] = $item->unit;
                $options['converter_reference'] = $item->converter;

                
                InventoryHelper::increase($form, $item->salesReturn->warehouse, $item->item, $item->quantity, $item->unit, $item->converter, $options);
            }
        }
    }

    public static function updateJournal($salesReturn)
    {
        $accountReceivable = new Journal;
        $accountReceivable->form_id = $salesReturn->form->id;
        $accountReceivable->journalable_type = 'Customer';
        $accountReceivable->journalable_id = $salesReturn->customer_id;
        $accountReceivable->chart_of_account_id = get_setting_journal('sales', 'account receivable');
        $accountReceivable->credit = $salesReturn->amount;
        $accountReceivable->save();

        $salesIncome = new Journal;
        $salesIncome->form_id = $salesReturn->form->id;
        $salesIncome->chart_of_account_id = get_setting_journal('sales', 'sales income');
        $salesIncome->debit = $salesReturn->amount - $salesReturn->tax;
        $salesIncome->save();

        foreach ($salesReturn->items as $item) {
            $amount = $item->item->cogs($item->item_id) * $item->quantity;

            $journalItem = new Journal;
            $journalItem->form_id = $salesReturn->form->id;
            $journalItem->journalable_type = Item::$morphName;
            $journalItem->journalable_id = $item->item_id;
            $journalItem->chart_of_account_id = $item->item->chart_of_account_id;
            $journalItem->debit = $amount;
            $journalItem->save();

            $costOfSales = new Journal;
            $costOfSales->form_id = $salesReturn->form->id;
            $costOfSales->journalable_type = Item::$morphName;
            $costOfSales->journalable_id = $item->item_id;
            $costOfSales->chart_of_account_id = get_setting_journal('sales', 'cost of sales');
            $costOfSales->credit = $amount;
            $costOfSales->save();
        }

        if ($salesReturn->tax > 0) {
            $taxPayable = new Journal;
            $taxPayable->form_id = $salesReturn->form->id;
            $taxPayable->chart_of_account_id = get_setting_journal('sales', 'income tax payable');
            $taxPayable->debit = $salesReturn->tax;
            $taxPayable->save();
        }
    }

    public function isAllowedToUpdate()
    {
        $this->isNotReferenced();
    }

    public function isAllowedToDelete()
    {
        $this->isNotReferenced();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by payments
        if ($this->paymentCollections->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by payment collection', $this->paymentCollections);
        }
    }
}
