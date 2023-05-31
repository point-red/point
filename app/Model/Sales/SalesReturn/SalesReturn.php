<?php

namespace App\Model\Sales\SalesReturn;

use App\Model\Form;
use App\Model\Token;
use App\Model\TransactionModel;
use App\Model\Accounting\Journal;
use App\Model\Master\Item;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Traits\Model\Sales\SalesReturnRelation;
use App\Traits\Model\Sales\SalesReturnJoin;
use Exception;
use App\Helpers\Inventory\InventoryHelper;
use App\Exceptions\IsReferencedException;
use Illuminate\Support\Facades\Mail;
use App\Mail\Sales\SalesReturnApprovalRequest;
use App\Model\Sales\SalesInvoice\SalesInvoiceReference;

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
        self::validate($data);
        $salesReturn = new self;
        $salesReturn->fill($data);

        $items = self::mapItems($data['items'] ?? []);
        $services = self::mapServices($data['services'] ?? []);

        $salesReturn->checkQuantity($items);

        $salesReturn->save();
        $salesReturn->items()->saveMany($items);

        self::checkJournalBalance($salesReturn);
        //$salesReturn->services()->saveMany($services);

        $form = new Form;
        $form->saveData($data, $salesReturn);

        return $salesReturn;
    }

    private static function validate($data)
    {
        $salesInvoice = SalesInvoice::where('id', $data['sales_invoice_id'])->first();
        if ($salesInvoice->form->done === 1) {
            throw new Exception('Sales return form already done', 422);
        }

        $subTotal = 0;
        foreach ($data['items'] as $item) {
            $total = round($item['quantity'] * ($item['price'] - $item['discount_value']), 12);
            if ($total != round($item['total'], 10)) {
                throw new Exception('total for item ' .$item['item_name']. ' should be ' .$total , 422);
            }
            $subTotal = $subTotal + $total;
        }

        if (round($data['sub_total'], 10) != round($subTotal, 10)) {
            throw new Exception('sub total should be ' .$subTotal , 422);
        }

        $taxBase = $subTotal;
        if (round($data['tax_base'], 10) != round($taxBase, 10)) {
            throw new Exception('tax base should be ' .$taxBase , 422);
        }

        if ($data['type_of_tax'] != $salesInvoice->type_of_tax) {
            throw new Exception('type of tax should be same with invoice' , 422);
        }

        $tax = round($taxBase * (10 / 110), 10);
        if (round($data['tax'], 10) != $tax) {
            throw new Exception('tax should be ' .$tax , 422);
        }

        $total = 0;
        if ($data['type_of_tax'] === 'include') {
            $total = $taxBase;
        } else {
            $total = $taxBase + $tax ;
        }
        
        if (round($data['amount'], 10) != round($total, 10)) {
            throw new Exception('amount should be ' .$total , 422);
        }
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

    public static function checkJournalBalance($salesReturn) {
        $ar = get_setting_journal('sales', 'account receivable');
        $salesIncome = get_setting_journal('sales', 'sales income');

        $credit = $salesReturn->amount;
        $debit = $salesReturn->amount - $salesReturn->tax;
        foreach ($salesReturn->items as $item) {
            $amount = $item->item->cogs($item->item_id) * $item->quantity;
            $debit = $debit + $amount;
            $credit = $credit + $amount;
        }

        $debit = $debit + $salesReturn->tax;
        if (round($debit, 10) != round($credit, 10)) {
            throw new Exception('Journal not balance', 422);
        }

        return [
            'debit' => $debit,
            'credit' => $credit
        ];
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

    public static function updateSalesInvoiceReference($salesReturn)
    {
        $invoiceReference = new SalesInvoiceReference;
        $invoiceReference->sales_invoice_id = $salesReturn->sales_invoice_id;
        $invoiceReference->referenceable_id = $salesReturn->id;
        $invoiceReference->referenceable_type = 'SalesReturn';
        $invoiceReference->amount = $salesReturn->amount;
        $invoiceReference->save();
    }

    public function isAllowedToUpdate()
    {
        $this->isNotReferenced();
        $this->isNotDone();
    }

    public function isAllowedToDelete()
    {
        $this->isNotReferenced();
        $this->isNotDone();
    }

    private function isNotReferenced()
    {
        // Check if not referenced by payments
        if ($this->paymentCollections->count()) {
            throw new IsReferencedException('form referenced by payment collection', $this->paymentCollections);
        }
    }

    private function isNotDone()
    {
        if ($this->form->done === 1) {
            throw new Exception('form already done', 422);
        }
    }

    public static function sendApproval($salesReturns)
    {
        $salesReturnByApprovers = [];

        $sendBy = tenant(auth()->user()->id);

        $salesReturnByApprovers[$salesReturns->form->request_approval_to][] = $salesReturns;

        foreach ($salesReturnByApprovers as $salesReturnByApprover) {
            $approver = null;

            $formStart = head($salesReturnByApprover)->form;
            $formEnd = last($salesReturnByApprover)->form;

            $form = [
                'number' => $formStart->number,
                'date' => $formStart->date,
                'created' => $formStart->created_at,
                'send_by' => $sendBy
            ];

            // loop each sales return by group approver
            foreach ($salesReturnByApprover as $salesReturn) {
                $salesReturn->action = 'create';
                
                if(!$approver) {
                    $approver = $salesReturn->form->requestApprovalTo;
                    // create token based on request_approval_to
                    $approverToken = Token::where('user_id', $approver->id)->first();
                    if (!$approverToken) {
                        $approverToken = new Token();
                        $approverToken->user_id = $approver->id;
                        $approverToken->token = md5($approver->email.''.now());
                        $approverToken->save();
                    }

                    $approver->token = $approverToken->token;
                }
                
                if ($salesReturn->form->close_status === 0) $salesReturn->action = 'close';

                if (
                    $salesReturn->form->cancellation_status === 0
                    && $salesReturn->form->close_status === null
                ) {
                    $salesReturn->action = 'delete';
                }
            }

            $approvalRequest = new SalesReturnApprovalRequest($salesReturnByApprover, $approver, (object) $form, $_SERVER['HTTP_REFERER']);
            Mail::to([ $approver->email ])->queue($approvalRequest);
        }
    }
}
