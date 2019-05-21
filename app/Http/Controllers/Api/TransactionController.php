<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Finance\Payment\Payment;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Http\Requests\Transaction\StoreTransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return ApiResource
     */
    public function store(StoreTransactionRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $result = new \stdClass();
        /* PURCHASE */
        if ($request->has('purchase.supplier_id')) {
            $requestData = $request->get('purchase');
            /* PURCHASE RECEIVE */
            if ($request->has('purchase_receive_number')) {
                $requestData['number'] = $request->get('purchase_receive_number');
            }
            $purchaseReceive = PurchaseReceive::create($requestData);
            $result->purchase_receive = new ApiResource($purchaseReceive);

            /* PURCHASE INVOICE */
            $requestData = $request->get('purchase');
            if ($request->has('purchase_invoice_number')) {
                $requestData['number'] = $request->get('purchase_invoice_number');
            }

            $requestData['items'] = array_map(function ($item) use ($purchaseReceive) {
                $purchaseReceiveItems = $purchaseReceive->items;
                $purchaseReceiveItem = $purchaseReceiveItems->firstWhere('item_id', $item['item_id']);

                $item['purchase_receive_item_id'] = $purchaseReceiveItem->id;
                $item['purchase_receive_id'] = $purchaseReceive->id;

                return $item;
            }, $requestData['items']);

            $purchaseInvoice = PurchaseInvoice::create($requestData);
            $result->purchase_invoice = new ApiResource($purchaseInvoice);

            /* PURCHASE PAYMENT */
            if ($request->has('purchase.payment')) {
                $purchasePaymentRequest = [
                    'disbursed' => true,
                    'payment_type' => $request->get('purchase.payment.type'),
                    'paymentable_id' => $request->get('purchase.supplier_id'),
                    'paymentable_type' => Supplier::$morphName,
                    'details' => [
                        'chart_of_account_id' => $request->get('purchase.payment.chart_of_account_id'),
                        'allocation_id' => $request->get('purchase.payment.allocation_id'),
                        'amount' => $purchaseInvoice->amount,
                        'referenceable_id' => $purchaseInvoice->id,
                        'referenceable_type' => PurchaseInvoice::$morphName,
                    ],
                ];
                if ($request->has('purchase_payment_number')) {
                    $purchasePaymentRequest['number'] = $request->get('purchase_payment_number');
                }
                $purchasePayment = Payment::create($purchasePaymentRequest);
                $request->purchase_payment = $purchasePayment;
            }
        }

        // Insert Sales Order, Delivery Order, Delivery Note if customer_id and items is provided
        if ($request->has('sales.customer_id')) {
            $requestData = $request->get('sales');
            /* DELIVERY ORDER */
            if ($request->has('delivery_order_number')) {
                $requestData['number'] = $request->get('delivery_order_number');
            }
            $deliveryOrder = DeliveryOrder::create($requestData);

            /* DELIVERY NOTE */
            if ($request->has('delivery_note_number')) {
                $requestData['number'] = $request->get('delivery_note_number');
                $requestData['delivery_order_id'] = $deliveryOrder->id;
            }
            $requestData['items'] = array_map(function ($item) use ($deliveryOrder) {
                $deliveryOrderItems = $deliveryOrder->items;
                $deliveryOrderItem = $deliveryOrderItems->firstWhere('item_id', $item['item_id']);

                $item['delivery_order_item_id'] = $deliveryOrderItem->id;
                $item['delivery_order_id'] = $deliveryOrder->id;

                return $item;
            }, $requestData['items']);
            $deliveryNote = DeliveryNote::create($requestData);
            $result->delivery_note = new ApiResource($deliveryNote);

            /* SALES INVOICE */
            $requestData = $request->get('sales');
            if ($request->has('sales_invoice_number')) {
                $requestData['number'] = $request->get('sales_invoice_number');
            }
            $requestData['items'] = array_map(function ($item) use ($deliveryNote) {
                $deliveryNoteItems = $deliveryNote->items;
                $deliveryNoteItem = $deliveryNoteItems->firstWhere('item_id', $item['item_id']);

                $item['delivery_note_item_id'] = $deliveryNoteItem->id;
                $item['delivery_note_id'] = $deliveryNote->id;

                return $item;
            }, $requestData['items']);
            $salesInvoice = SalesInvoice::create($requestData);
            $result->sales_invoice = new ApiResource($salesInvoice);

            /* SALES PAYMENT */
            if ($request->has('sales_payment_type')) {
                $salesPaymentRequest = [
                    'disbursed' => false,
                    'payment_type' => $request->get('sales_payment_type'),
                    'paymentable_id' => $request->get('sales.customer'),
                    'paymentable_type' => Customer::$morphName,
                    'details' => [
                        'chart_of_account_id' => $request->get('sales_payment_chart_of_account_id'),
                        'allocation_id' => $request->get('sales_payment_allocation_id'),
                        'amount' => $salesInvoice->amount,
                        'referenceable_id' => $salesInvoice->id,
                        'referenceable_type' => SalesInvoice::$morphName,
                    ],
                ];
                if ($request->has('sales_payment_number')) {
                    $salesPaymentRequest['number'] = $request->get('sales_payment_number');
                }

                $salesPayment = Payment::create($salesPaymentRequest);
                $request->sales_payment = $salesPayment;
            }
        }

        DB::connection('tenant')->commit();

        return response()->json([
            'data' => $result,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function createPurchase()
    {
    }

    private function createSales()
    {
    }
}
