<?php

namespace App\Traits\Model\Sales;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use App\Model\Master\Allocation;
use App\Model\Accounting\ChartOfAccount;

trait PaymentCollectionJoin
{
    public static function joins($query, $joins)
    {
        $joins = explode(',', $joins);

        if (! $joins) {
            return $query;
        }

        if (in_array('customer', $joins)) {
            $query = $query->join(Customer::getTableName().' as '.Customer::$alias, function ($q) {
                $q->on(PaymentCollection::$alias.'.customer_id', '=', Customer::$alias.'.id');
            });
        }

        if (in_array('form', $joins)) {
            $query = $query->join(Form::getTableName().' as '.Form::$alias, function ($q) {
                $q->on(Form::$alias.'.formable_id', '=', PaymentCollection::$alias.'.id')
                    ->where(Form::$alias.'.formable_type', PaymentCollection::$morphName);
            });
        }

        if (in_array('details', $joins)) {
            $query = $query->leftjoin(PaymentCollectionDetail::getTableName().' as '.PaymentCollectionDetail::$alias,
                PaymentCollectionDetail::$alias.'.sales_payment_collection_id', '=', PaymentCollection::$alias.'.id');
            if (in_array('formdetail', $joins)) {
                $query = $query->leftjoin(Form::getTableName().' as formdetail', function ($q) {
                    $q->on('formdetail.formable_id', '=', PaymentCollectionDetail::$alias.'.referenceable_id')
                        ->where('formdetail.formable_type', PaymentCollectionDetail::$alias.'.referenceable_type');
                });
            }
            if (in_array('account', $joins)) {
                $query = $query->leftjoin(ChartOfAccount::getTableName().' as '.ChartOfAccount::$alias,
                    ChartOfAccount::$alias.'.id', '=', PaymentDetail::$alias.'.chart_of_account_id');
            }
            if (in_array('allocation', $joins)) {
                $query = $query->leftjoin(Allocation::getTableName().' as '.Allocation::$alias,
                    Allocation::$alias.'.id', '=', PaymentDetail::$alias.'.allocation_id');
            }
        }

        return $query;
    }
}
