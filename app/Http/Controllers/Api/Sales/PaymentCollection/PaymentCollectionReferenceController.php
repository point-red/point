<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;

class PaymentCollectionReferenceController extends Controller
{

    public function customerSalesForms(Request $request, $customerId)
    {
        $forms = [];
        $salesInvoice = SalesInvoice::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesInvoice::getTableName('id'))
            ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                ' from '.PaymentCollectionDetail::getTableName().
                ' join '.PaymentCollection::getTableName().
                ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                ' join '.Form::getTableName().
                ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                '" and '.Form::getTableName('approval_status').' = 1 and ('.Form::getTableName('cancellation_status').
                ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                ' = "'.SalesInvoice::$morphName.'") collected'),
                function($join) {
                    $join->on(SalesInvoice::getTableName('id'), '=', 'collected.referenceable_id');
                }
            )
            ->where(Form::getTableName('formable_type'), SalesInvoice::$morphName)
            ->where(SalesInvoice::getTableName('customer_id'), $customerId)
            ->where(Form::getTableName('done'), 0)
            ->where(Form::getTableName('approval_status'), 1)
            ->orderBy(Form::getTableName('date'), 'desc')
            ->select(
                Form::getTableName('date'),
                Form::getTableName('number'),
                Form::getTableName('notes'),
                SalesInvoice::getTableName('id'),
                SalesInvoice::getTableName('amount'),
                (DB::raw('cast(sum(coalesce(collected.amount,0)) as decimal) as collected')),
                (DB::raw('cast(('.SalesInvoice::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
            )
            ->groupBy(SalesInvoice::getTableName('id'))
            ->havingRaw('toCollect > 0')
            ->get();

        $salesDownPayment = SalesDownPayment::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesDownPayment::getTableName('id'))
            ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                ' from '.PaymentCollectionDetail::getTableName().
                ' join '.PaymentCollection::getTableName().
                ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                ' join '.Form::getTableName().
                ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                '" and '.Form::getTableName('approval_status').' = 1 and ('.Form::getTableName('cancellation_status').
                ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                ' = "'.SalesDownPayment::$morphName.'") collected'),
                function($join) {
                    $join->on(SalesDownPayment::getTableName('id'), '=', 'collected.referenceable_id');
                }
            )
            ->where(Form::getTableName('formable_type'), SalesDownPayment::$morphName)
            ->where(SalesDownPayment::getTableName('customer_id'), $customerId)
            ->where(Form::getTableName('done'), 1)
            ->where(Form::getTableName('approval_status'), 1)
            ->orderBy(Form::getTableName('date'), 'desc')
            ->select(
                Form::getTableName('date'),
                Form::getTableName('number'),
                Form::getTableName('notes'),
                SalesDownPayment::getTableName('id'),
                SalesDownPayment::getTableName('amount'),
                (DB::raw('cast(sum(coalesce(collected.amount,0)) as decimal) as collected')),
                (DB::raw('cast(('.SalesDownPayment::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
            )
            ->groupBy(SalesDownPayment::getTableName('id'))
            ->havingRaw('toCollect > 0')
            ->get();

        $salesReturn = SalesReturn::join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesReturn::getTableName('id'))
            ->leftJoin(DB::raw('(SELECT '.PaymentCollectionDetail::getTableName().'.* '.
                ' from '.PaymentCollectionDetail::getTableName().
                ' join '.PaymentCollection::getTableName().
                ' on '.PaymentCollection::getTableName('id').' = '.PaymentCollectionDetail::getTableName('sales_payment_collection_id').
                ' join '.Form::getTableName().
                ' on '.Form::getTableName('formable_id').' = '.PaymentCollection::getTableName('id').
                ' and '.Form::getTableName('formable_type').' = "'.PaymentCollection::$morphName.
                '" and '.Form::getTableName('approval_status').' = 1 and ('.Form::getTableName('cancellation_status').
                ' IS NULL OR '.Form::getTableName('cancellation_status').' != 1) where '.PaymentCollectionDetail::getTableName('referenceable_type').
                ' = "'.SalesReturn::$morphName.'") collected'),
                function($join) {
                    $join->on(SalesReturn::getTableName('id'), '=', 'collected.referenceable_id');
                }
            )
            ->where(Form::getTableName('formable_type'), SalesReturn::$morphName)
            ->where(SalesReturn::getTableName('customer_id'), $customerId)
            ->where(Form::getTableName('done'), 0)
            ->where(Form::getTableName('approval_status'), 1)
            ->orderBy(Form::getTableName('date'), 'desc')
            ->select(
                Form::getTableName('date'),
                Form::getTableName('number'),
                Form::getTableName('notes'),
                SalesReturn::getTableName('id'),
                SalesReturn::getTableName('amount'),
                (DB::raw('cast(sum(coalesce(collected.amount,0)) as decimal) as collected')),
                (DB::raw('cast(('.SalesReturn::getTableName('amount').' - sum(coalesce(collected.amount,0))) as decimal) as toCollect'))
            )
            ->groupBy(SalesReturn::getTableName('id'))
            ->havingRaw('toCollect > 0')
            ->get();
            
        $forms['salesInvoice'] = $salesInvoice;
        $forms['salesDownPayment'] = $salesDownPayment;
        $forms['salesReturn'] = $salesReturn;
        return new ApiResource($forms);
    }
}
