<?php

namespace App\Http\Controllers\Api;

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
    public function store(Request $request)
    {
        DB::connection('tenant')->beginTransaction();

        $result = new \stdClass();

        // TODO validation items and services using required_without
        // https://laravel.com/docs/5.7/validation#rule-required-without

        $request->validate([
            'date' => 'bail|required|date',
            'items' => 'required_without:services',
            'services' => 'required_without:items',
        ]);

        if ($request->has('items')) {
            $request->validate([
                'items.*.item_id' => 'bail|required|integer|min:1|exists:tenant.items,id',
                'items.*.gross_weight' => 'bail|nullable|numeric|min:0',
                'items.*.tare_weight' => 'bail|nullable|numeric|min:0',
                'items.*.net_weight' => 'bail|nullable|numeric|min:0',
                'items.*.quantity' => 'bail|required|numeric|min:0',
                'items.*.purchase_price' => 'bail|required_with:supplier_id|numeric|min:0',
                'items.*.sell_price' => 'bail|required_with:customer_id|numeric|min:0',
                'items.*.discount_percent' => 'bail|nullable|numeric|min:0',
                'items.*.discount_value' => 'bail|numeric|min:0',
                'items.*.taxable' => 'bail|boolean',
                'items.*.unit' => 'bail|required|string|max:255',
                'items.*.converter' => 'bail|required|numeric|min:0',
                'items.*.allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                'warehouse_id' => 'bail|required|integer|min:1|exists:tenant.warehouses,id',
            ]);
        }

        if ($request->has('services')) {
            $request->validate([
                'services.*.service_id' => 'bail|required|integer|min:1|exists:tenant.services,id',
            ]);
        }

        // Insert Purchase Receive if supplier_id and items is provided
        if ($request->has('supplier_id')) {
            $request->validate([
                'supplier_id' => 'bail|integer|min:1|exists:tenant.suppliers,id',
                'due_date' => 'bail|required|date',
                'delivery_fee' => 'bail|numeric|min:0',
                'discount_value' => 'bail|numeric|min:0',
                'type_of_tax' => 'bail|required|in:include,exclude,non',
                'tax' => 'bail|required|numeric|min:0',
            ]);

            $purchaseReceive = PurchaseReceive::create($request->all());
            $result->purchase_receive = new ApiResource($purchaseReceive);

            $purchaseInvoiceRequest = $request->all();
            $purchaseInvoiceRequest['purchase_receive_ids'] = [$purchaseReceive->id];
            $purchaseInvoice = PurchaseInvoice::create($purchaseInvoiceRequest);
            $result->purchase_invoice = new ApiResource($purchaseInvoice);

            if ($request->has('purchase_payment_type')) {
                $request->validate([
                    'purchase_payment_type' => 'bail|string|min:1',
                    'purchase_payment_chart_of_account_id' => 'bail|required|integer|min:1|exists:tenant.chart_of_accounts,id',
                    'purchase_payment_allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                ]);

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
            $request->validate([
                'customer_id' => 'bail|integer|min:1|exists:tenant.customers,id',
                'eta' => 'bail|required|date',
                'due_date' => 'bail|required|date',
                'cash_only' => 'boolean',
                'need_down_payment' => 'boolean',
                'delivery_fee' => 'bail|numeric|min:0',
                'discount_value' => 'bail|numeric|min:0',
                'type_of_tax' => 'bail|required|in:include,exclude,non',
                'tax' => 'bail|required|numeric|min:0',
            ]);

            // Is Sales Order and Delivery Order it really required to make Delivery Note?
            // $salesOrder = SalesOrder::create($request->all());
            // $result->sales_order = new ApiResource($salesOrder);

            // $deliveryOrderRequest = $request->all();
            // $deliveryOrderRequest['sales_order_id'] = $salesOrder->id;
            // $deliveryOrder = DeliveryOrder::create($deliveryOrderRequest);
            // $result->delivery_order = new ApiResource($deliveryOrder);

            // $deliveryNoteRequest = $request->all();
            // $deliveryNoteRequest['delivery_order_id'] = $deliveryOrder->id;
            $deliveryNote = DeliveryNote::create($request->all());
            $result->delivery_note = new ApiResource($deliveryNote);

            $salesInvoiceRequest = $request->all();
            $salesInvoiceRequest['delivery_note_ids'] = [$deliveryNote->id];
            $salesInvoice = SalesInvoice::create($salesInvoiceRequest);
            $result->sales_invoice = new ApiResource($salesInvoice);

            if ($request->has('sales_payment_type')) {
                $request->validate([
                    'sales_payment_type' => 'bail|string|min:1',
                    'sales_payment_chart_of_account_id' => 'bail|required|integer|min:1|exists:tenant.chart_of_accounts,id',
                    'sales_payment_allocation_id' => 'bail|nullable|integer|min:1|exists:tenant.allocations,id',
                ]);

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
}
