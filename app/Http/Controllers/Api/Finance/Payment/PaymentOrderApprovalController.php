<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use Illuminate\Http\Request;

class PaymentOrderApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function approve(Request $request, $id)
    {
        $paymentOrder = PaymentOrder::findOrFail($id);
        $paymentOrder->form->approval_by = auth()->user()->id;
        $paymentOrder->form->approval_at = now();
        $paymentOrder->form->approval_status = 1;
        $paymentOrder->form->save();

        return new ApiResource($paymentOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws ApprovalNotFoundException
     * @throws UnauthorizedException
     */
    public function reject(Request $request, $id)
    {
        $paymentOrder = PaymentOrder::findOrFail($id);
        $paymentOrder->form->approval_by = auth()->user()->id;
        $paymentOrder->form->approval_at = now();
        $paymentOrder->form->approval_reason = $request->get('reason');
        $paymentOrder->form->approval_status = -1;
        $paymentOrder->form->save();

        return new ApiResource($paymentOrder);
    }
}
