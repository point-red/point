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

        $approvalMatch = null;

        if ($formula->form->approvals->count() == 0) {
            throw new ApprovalNotFoundException();
        }

        foreach ($formula->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    break;
                }
            }
            if ($approval->requested_to == auth()->user()->id) {
                $approvalMatch = $approval;
                break;
            }
        }

        if ($approvalMatch == null) {
            throw new UnauthorizedException();
        } else {
            $formula->form->approved = true;
            $formula->form->save();
            $approvalMatch->approval_at = now();
            $approvalMatch->approved = true;
            $approvalMatch->save();
        }

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

        $approvalMatch = null;

        if ($formula->form->approvals->count() == 0) {
            throw new ApprovalNotFoundException();
        }

        foreach ($formula->form->approvals as $approval) {
            if (!auth()->user()) {
                if ($request->get('token') == $approval->token) {
                    $approvalMatch = $approval;
                    break;
                }
            }
            if ($approval->requested_to == auth()->user()->id) {
                $approvalMatch = $approval;
                break;
            }
        }

        if ($approvalMatch == null) {
            throw new UnauthorizedException();
        } else {
            $formula->form->approved = false;
            $formula->form->save();
            $approvalMatch->approval_at = now();
            $approvalMatch->approved = false;
            $approvalMatch->save();
        }

        return new ApiResource($formula);
    }
}
