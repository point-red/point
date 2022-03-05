<?php

namespace App\Http\Controllers\Api\Finance\CashAdvance;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Finance\CashAdvance\CashAdvance;
use Illuminate\Http\Request;

class CashAdvanceCancellationApprovalController extends Controller
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
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->form->cancellation_approval_by = auth()->user()->id;
        $cashAdvance->form->cancellation_approval_at = now();
        $cashAdvance->form->cancellation_status = 1;
        $cashAdvance->form->save();

        $cashAdvance->mapHistory($cashAdvance, $request->all());

        return new ApiResource($cashAdvance);
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
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->form->cancellation_approval_by = auth()->user()->id;
        $cashAdvance->form->cancellation_approval_at = now();
        $cashAdvance->form->cancellation_approval_reason = $request->get('reason');
        $cashAdvance->form->cancellation_status = -1;
        $cashAdvance->form->save();

        $cashAdvance->mapHistory($cashAdvance, $request->all());
        
        return new ApiResource($cashAdvance);
    }
}
