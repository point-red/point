<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Model\Finance\Payment\Payment;
use Illuminate\Http\Request;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

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

        if ($request->has('supplier_id')) {
            $purchaseData = $request->all();
            if ($request->has('purchase_receive_number')) {
                $purchaseData['number'] = $request->get('purchase_receive_number');
                foreach ($purchaseData['items'] as $items) {
                    $purchaseData['items']['price'] = $purchaseData['items']['purchase']['price'];
                    $purchaseData['items']['discount_percent'] = $purchaseData['items']['purchase']['discount_percent'];
                    $purchaseData['items']['discount_value'] = $purchaseData['items']['purchase']['discount_value'];
                    $purchaseData['items']['allocation_id'] = $purchaseData['items']['purchase']['allocation_id'];
                }
            }
            $purchaseReceive = PurchaseReceive::create($purchaseData);
            $result->purchase_receive = new ApiResource($purchaseReceive);

            $purchaseData = $request->all();
            if ($request->has('purchase_invoice_number')) {
                $purchaseData['number'] = $request->get('purchase_invoice_number');
            }
            $purchaseInvoiceRequest['purchase_receive_ids'] = [$purchaseReceive->id];
            $purchaseInvoice = PurchaseInvoice::create($purchaseInvoiceRequest);
            $result->purchase_invoice = new ApiResource($purchaseInvoice);

            if ($request->has('purchase_payment_type')) {
                $purchasePaymentRequest = [
                    'disbursed' => true,
                    'payment_type' => $request->get('purchase_payment_type'),
                    'paymentable_id' => $request->get('supplier_id'),
                    'paymentable_type' => Supplier::class,
                    'details' => [
                        'chart_of_account_id' => $request->get('purchase_payment_chart_of_account_id'),
                        'allocation_id' => $request->get('purchase_payment_allocation_id'),
                        'amount' => $purchaseInvoice->amount,
                        'referenceable_id' => $purchaseInvoice->id,
                        'referenceable_type' => PurchaseInvoice::class,
                    ],
                ];

                $purchasePayment = Payment::create($purchasePaymentRequest);
                $request->purchase_payment = $purchasePayment;
            }
        }

        // Insert Sales Order, Delivery Order, Delivery Note if customer_id and items is provided
        if ($request->has('customer_id')) {
            $deliveryNote = DeliveryNote::create($request->all());
            $result->delivery_note = new ApiResource($deliveryNote);

            $salesInvoiceRequest = $request->all();
            $salesInvoiceRequest['delivery_note_ids'] = [$deliveryNote->id];
            $salesInvoice = SalesInvoice::create($salesInvoiceRequest);
            $result->sales_invoice = new ApiResource($salesInvoice);

            if ($request->has('sales_payment_type')) {
                $salesPaymentRequest = [
                    'disbursed' => false,
                    'payment_type' => $request->get('sales_payment_type'),
                    'paymentable_id' => $request->get('supplier_id'),
                    'paymentable_type' => Customer::class,
                    'details' => [
                        'chart_of_account_id' => $request->get('sales_payment_chart_of_account_id'),
                        'allocation_id' => $request->get('sales_payment_allocation_id'),
                        'amount' => $salesInvoice->amount,
                        'referenceable_id' => $salesInvoice->id,
                        'referenceable_type' => SalesInvoice::class,
                    ],
                ];

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
