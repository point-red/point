<?php

namespace App\Model\Sales\PaymentCollection;

use App\Exceptions\IsReferencedException;
use App\Model\Finance\Payment\Payment;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\TransactionModel;
use App\Traits\Model\Sales\PaymentCollectionJoin;
use App\Traits\Model\Sales\PaymentCollectionRelation;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\Log;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AmountCollectedInvalidException;

class PaymentCollection extends TransactionModel
{
    use PaymentCollectionJoin;

    public static $morphName = 'PaymentCollection';

    protected $connection = 'tenant';

    public static $alias = 'sales_payment_collection';

    public $defaultNumberPrefix = 'PC';

    protected $table = 'sales_payment_collections';

    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'due_date',
        'payment_account_id',
        'amount',
        'supplier_id',
        'supplier_name',
        'payment_id',
        'customer_id',
        'customer_name',
    ];

    protected $casts = [
        'amount' => 'double'
    ];

    public function form()
    {
        return $this->morphOne(Form::class, 'formable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(PaymentCollectionDetail::class, 'sales_payment_collection_id');
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'referenceable', 'payment_details')
            ->join(Form::getTableName(), function ($q) {
                $q->on(Form::getTableName('formable_id'), '=', Payment::getTableName('id'))
                    ->where(Form::getTableName('formable_type'), Payment::$morphName);
            })
            ->whereNotNull(Form::getTableName('number'))
            ->where(function ($q) {
                $q->whereNull(Form::getTableName('cancellation_status'))
                    ->orWhere(Form::getTableName('cancellation_status'), '!=', '1');
            });
    }

    public static function create($data)
    {
        $paymentCollection = new self;
        $paymentCollection->fill($data);
        $paymentCollectionDetails = self::mapDetails($data['details'] ?? []);
        
        $paymentCollection->amount = self::calculateAmount($paymentCollectionDetails);
        $paymentCollection->save();
        
        $paymentCollection->details()->saveMany($paymentCollectionDetails);
        
        $form = new Form;
        $form->saveData($data, $paymentCollection);

        return $paymentCollection;
    }

    private static function mapDetails($details)
    {
        
        return array_map(function ($detail) {
            $paymentCollectionDetail = new PaymentCollectionDetail;
            $paymentCollectionDetail->fill($detail);
            if ($paymentCollectionDetail->referenceable_type) {
                if ($paymentCollectionDetail->referenceable_type === 'SalesInvoice') {
                    $reference = SalesInvoice::findOrFail($paymentCollectionDetail->referenceable_id);
                }
                if ($paymentCollectionDetail->referenceable_type === 'SalesDownPayment') {
                    $reference = SalesDownPayment::findOrFail($paymentCollectionDetail->referenceable_id);
                }
                if ($paymentCollectionDetail->referenceable_type === 'SalesReturn') {
                    $reference = SalesReturn::findOrFail($paymentCollectionDetail->referenceable_id);
                }
            }

            return $paymentCollectionDetail;
        }, $details);
    }

    private static function calculateAmount($paymentCollectionDetails) {
        $amount = 0;

        foreach ($paymentCollectionDetails as $detail) {
            if ($detail->chart_of_account_id) {

                $coa = ChartOfAccount::findOrFail($detail->chart_of_account_id);

                if($coa->type->is_debit === 0) {
                    $amount += $detail->amount;
                } else {
                    $amount -= $detail->amount;
                }
            } else {
                if ($detail->referenceable_type === 'SalesInvoice') {
                    $amount += $detail->amount;
                }
                if ($detail->referenceable_type === 'SalesDownPayment') {
                    $amount -= $detail->amount;
                }
                if ($detail->referenceable_type === 'SalesReturn') {
                    $amount -= $detail->amount;
                }
            }
        }
        return $amount;
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
        if ($this->payments->count()) {
            throw new IsReferencedException('Cannot edit form because referenced by payments', $this->payments);
        }
    }

    /**
     * Update price, cogs in inventory.
     *
     * @param $form
     * @param $transferItem
     */
    public static function checkAvailableReference($paymentCollection)
    {
        $isSuccess = true;
        foreach ($paymentCollection->details as $detail) {
            if ($detail->referenceable_type === 'SalesInvoice') {
                $toCollect = SalesInvoice::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesInvoice::getTableName('id'))
                    ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                        ' from '.PaymentCollectionDetail::getTableName().
                        ' join '.PaymentCollection::getTableName().
                        ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                        ' join '.Form::getTableName().
                        ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                        ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                        '" and '.Form::getTableName('approval_status').' = 1 and '.Form::getTableName('number').' IS NOT NULL and ('.Form::getTableName('cancellation_status').
                        ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                        ' = "'.SalesInvoice::$morphName.'" and '.PaymentCollectionDetail::getTableName('referenceable_id').
                        ' = "'.$detail->referenceable_id.'") collected'),
                        function($join) {
                            $join->on(SalesInvoice::getTableName('id'), '=', 'collected.referenceable_id');
                        }
                    )
                    ->where(Form::getTableName('formable_type'), SalesInvoice::$morphName)
                    ->where(SalesInvoice::getTableName('id'), $detail->referenceable_id)
                    ->select(
                        (DB::raw('cast(('.SalesInvoice::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
                    )
                    ->groupBy(SalesInvoice::getTableName('id'))
                    ->value('toCollect');

                    if ($toCollect < $detail->amount) {
                        $isSuccess = false;
                    }
            }

            if ($detail->referenceable_type === 'SalesDownPayment') {
                $toCollect = SalesDownPayment::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesDownPayment::getTableName('id'))
                    ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                        ' from '.PaymentCollectionDetail::getTableName().
                        ' join '.PaymentCollection::getTableName().
                        ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                        ' join '.Form::getTableName().
                        ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                        ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                        '" and '.Form::getTableName('approval_status').' = 1 and '.Form::getTableName('number').' IS NOT NULL and ('.Form::getTableName('cancellation_status').
                        ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                        ' = "'.SalesDownPayment::$morphName.'" and '.PaymentCollectionDetail::getTableName('referenceable_id').
                        ' = "'.$detail->referenceable_id.'") collected'),
                        function($join) {
                            $join->on(SalesDownPayment::getTableName('id'), '=', 'collected.referenceable_id');
                        }
                    )
                    ->where(Form::getTableName('formable_type'), SalesDownPayment::$morphName)
                    ->where(SalesDownPayment::getTableName('id'), $detail->referenceable_id)
                    ->select(
                        (DB::raw('cast(('.SalesDownPayment::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
                    )
                    ->groupBy(SalesDownPayment::getTableName('id'))
                    ->value('toCollect');

                    if ($toCollect < $detail->amount) {
                        $isSuccess = false;
                    }
            }

            if ($detail->referenceable_type === 'SalesReturn') {
                $toCollect = SalesReturn::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesReturn::getTableName('id'))
                    ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                        ' from '.PaymentCollectionDetail::getTableName().
                        ' join '.PaymentCollection::getTableName().
                        ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                        ' join '.Form::getTableName().
                        ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                        ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                        '" and '.Form::getTableName('approval_status').' = 1 and '.Form::getTableName('number').' IS NOT NULL and ('.Form::getTableName('cancellation_status').
                        ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                        ' = "'.SalesReturn::$morphName.'" and '.PaymentCollectionDetail::getTableName('referenceable_id').
                        ' = "'.$detail->referenceable_id.'") collected'),
                        function($join) {
                            $join->on(SalesReturn::getTableName('id'), '=', 'collected.referenceable_id');
                        }
                    )
                    ->where(Form::getTableName('formable_type'), SalesReturn::$morphName)
                    ->where(SalesReturn::getTableName('id'), $detail->referenceable_id)
                    ->select(
                        (DB::raw('cast(('.SalesReturn::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
                    )
                    ->groupBy(SalesReturn::getTableName('id'))
                    ->value('toCollect');

                    if ($toCollect < $detail->amount) {
                        $isSuccess = false;
                    }
            }
            
        }
        return $isSuccess;
    }
}