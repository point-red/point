<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use Illuminate\Http\Request;

class FormulaApprovalController extends Controller
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
        $formula = ManufactureFormula::findOrFail($id);
        $formula->form->approval_by = auth()->user()->id;
        $formula->form->approval_at = now();
        $formula->form->approval_status = 1;
        $formula->form->save();

        return new ApiResource($formula);
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
        $formula = ManufactureFormula::findOrFail($id);
        $formula->form->approval_by = auth()->user()->id;
        $formula->form->approval_at = now();
        $formula->form->approval_reason = $request->get('approval_reason');
        $formula->form->approval_status = -1;
        $formula->form->save();

        return new ApiResource($formula);
    }
}
