<?php

namespace App\Http\Controllers\Api\Sales\SalesQuotation;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesQuotation\SalesQuotation;
use Illuminate\Http\Request;

class SalesQuotationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $salesQuotation = SalesQuotation::findOrFail($id);
        $salesQuotation->form->approval_by = auth()->user()->id;
        $salesQuotation->form->approval_at = now();
        $salesQuotation->form->approval_status = 1;
        $salesQuotation->form->save();

        return new ApiResource($salesQuotation);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $salesQuotation = SalesQuotation::findOrFail($id);
        $salesQuotation->form->approval_by = auth()->user()->id;
        $salesQuotation->form->approval_at = now();
        $salesQuotation->form->approval_reason = $request->get('reason');
        $salesQuotation->form->approval_status = -1;
        $salesQuotation->form->save();

        return new ApiResource($salesQuotation);
    }
}
