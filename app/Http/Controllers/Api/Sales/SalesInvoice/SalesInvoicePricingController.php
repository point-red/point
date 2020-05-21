<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesInvoice\SalesInvoiceItem;
use Illuminate\Http\Request;

class SalesInvoicePricingController extends Controller
{
    public function lastPrice(Request $request, $itemId)
    {
        $customerId = $request->get('customer_id');
        $lastItem = SalesInvoiceItem::join(SalesInvoice::getTableName(), SalesInvoice::getTableName('id'), '=', SalesInvoiceItem::getTableName('sales_invoice_id'))
            ->join(Form::getTableName(), Form::getTableName('formable_id'), '=', SalesInvoice::getTableName('id'))
            ->where(Form::getTableName('formable_type'), SalesInvoice::$morphName)
            ->where(SalesInvoiceItem::getTableName('item_id'), $itemId)
            ->when($customerId, function ($query, $customerId) {
                $query->where(SalesInvoice::getTableName('customer_id'), $customerId);
            })
            ->orderBy(Form::getTableName('date'), 'desc')
            ->select(
                Form::getTableName('date'),
                Form::getTableName('number'),
                SalesInvoice::getTableName('id'),
                SalesInvoiceItem::getTableName('price'),
                SalesInvoiceItem::getTableName('notes'),
                SalesInvoiceItem::getTableName('discount_percent'),
                SalesInvoiceItem::getTableName('discount_value')
            )
            ->first();

        return new ApiResource($lastItem);
    }
}
