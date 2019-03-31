<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Model\Form;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesInvoice\SalesInvoiceItem;

class SalesInvoicePricingController extends Controller
{
    public function lastPrice($itemId, $customerId)
    {
        $salesInvoiceItem = SalesInvoiceItem::join(SalesInvoice::getTableName(), SalesInvoice::getTableName('id'), '=', SalesInvoiceItem::getTableName('sales_invoice_id'))
            ->join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesInvoice::getTableName('id'))
            ->where(Form::getTableName('formable_type'), SalesInvoice::class)
            ->where(SalesInvoiceItem::getTableName('item_id'), $itemId)
            ->where(SalesInvoice::getTableName('customer_id'), $customerId)
            ->orderBy(Form::getTableName('date'), 'desc')
            ->first();

        return new ApiResource($salesInvoiceItem);
    }
}
