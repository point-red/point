<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $paymentOrder = PaymentOrder::from(PaymentOrder::getTableName() . ' as ' . PaymentOrder::$alias)->eloquentFilter($request);

//        if ($request->get('filter_polymorphic')) {
//            $filter = json_decode($request->get('filter_polymorphic'), true);
//            $paymentOrder = $paymentOrder->where(function ($query) use ($filter) {
//                foreach ($filter as $key => $value) {
//                    $query->whereHasMorph('paymentable', [Customer::class, Supplier::class, Employee::class], function ($query) use ($key, $value) {
//                        $query->where($key, $value);
//                    });
//                }
//            });
//        }

        $paymentOrder = PaymentOrder::joins($paymentOrder, $request->get('join'));

        $paymentOrder = pagination($paymentOrder, $request->get('limit'));

        return new ApiCollection($paymentOrder);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $paymentOrder = PaymentOrder::create($request->all());

            $paymentOrder
                ->load('form')
                ->load('paymentable')
                ->load('details.referenceable')
                ->load('details.allocation');

            return new ApiResource($paymentOrder);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $paymentOrder = PaymentOrder::from(PaymentOrder::getTableName() . ' as ' . PaymentOrder::$alias)
            ->eloquentFilter($request)
            ->where(PaymentOrder::$alias . '.id', $id)
            ->first();

        return new ApiResource($paymentOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $paymentOrder = PaymentOrder::findOrFail($id);

            $paymentOrder->form->archive();
            $paymentOrder = PaymentOrder::create($request->all());
            $paymentOrder->load([
                'form',
                'paymentable',
                'details.referenceable',
                'details.allocation',
            ]);

            return new ApiResource($paymentOrder);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $paymentOrder = PaymentOrder::findOrFail($id);
        $paymentOrder->isAllowedToDelete();

        $paymentOrder->requestCancel($request);

//        if (! $response) {
//            foreach ($paymentOrder->details as $paymentOrderDetail) {
//                if ($paymentOrderDetail->referenceable) {
//                    $paymentOrderDetail->referenceable->form->done = false;
//                    $paymentOrderDetail->referenceable->form->save();
//                }
//            }
//        }

        return response()->json([], 204);
    }
}
