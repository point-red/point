<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use Illuminate\Http\Request;

class PaymentCollectionCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $paymentCollection = PaymentCollection::findOrFail($id);
        $paymentCollection->form->cancellation_approval_by = auth()->user()->id;
        $paymentCollection->form->cancellation_approval_at = now();
        $paymentCollection->form->cancellation_status = 1;
        $paymentCollection->form->save();

        return new ApiResource($paymentCollection);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        if ($request->get('reason') === null) {
            throw new PointException();
        }
        $paymentCollection = PaymentCollection::findOrFail($id);
        $paymentCollection->form->cancellation_approval_by = auth()->user()->id;
        $paymentCollection->form->cancellation_approval_at = now();
        $paymentCollection->form->cancellation_approval_reason = $request->get('reason');
        $paymentCollection->form->cancellation_status = -1;
        $paymentCollection->form->save();

        return new ApiResource($paymentCollection);
    }
}
