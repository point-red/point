<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $paymentOrder = PaymentOrder::from(PaymentOrder::getTableName().' as '.PaymentOrder::$alias)->eloquentFilter($request);

        $paymentOrder = PaymentOrder::joins($paymentOrder, $request->get('join'));

        $paymentOrder = pagination($paymentOrder, $request->get('limit'));

        return new ApiCollection($paymentOrder);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function store(Request $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $paymentOrder = PaymentOrder::create($request->all());

            $paymentOrder
                ->load('form')
                ->load('paymentable')
                ->load('details.referenceable')
                ->load('details.allocation');

            return new ApiResource($paymentOrder);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $paymentOrder = PaymentOrder::from(PaymentOrder::getTableName().' as '.PaymentOrder::$alias)
            ->eloquentFilter($request)
            ->where(PaymentOrder::$alias.'.id', $id)
            ->first();

        return new ApiResource($paymentOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
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
     * @return Response
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
