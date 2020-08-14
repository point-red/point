<?php

namespace App\Http\Controllers\Api\Sales\SalesOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesOrder\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->form->approval_by = auth()->user()->id;
        $salesOrder->form->approval_at = now();
        $salesOrder->form->approval_status = 1;
        $salesOrder->form->save();

        return new ApiResource($salesOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->form->approval_by = auth()->user()->id;
        $salesOrder->form->approval_at = now();
        $salesOrder->form->approval_reason = $request->get('reason');
        $salesOrder->form->approval_status = -1;
        $salesOrder->form->save();

        return new ApiResource($salesOrder);
    }
}
