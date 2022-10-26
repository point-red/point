<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Finance\Payment\Payment;
use Illuminate\Http\Request;

class PaymentCancellationApprovalController extends Controller
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
        $Payment = Payment::findOrFail($id);
        $Payment->form->cancellation_approval_by = auth()->user()->id;
        $Payment->form->cancellation_approval_at = now();
        $Payment->form->cancellation_status = 1;
        $Payment->form->save();

        return new ApiResource($Payment);
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
        $Payment = Payment::findOrFail($id);
        $Payment->form->cancellation_approval_by = auth()->user()->id;
        $Payment->form->cancellation_approval_at = now();
        $Payment->form->cancellation_approval_reason = $request->get('reason');
        $Payment->form->cancellation_status = -1;
        $Payment->form->save();

        return new ApiResource($Payment);
    }
}
