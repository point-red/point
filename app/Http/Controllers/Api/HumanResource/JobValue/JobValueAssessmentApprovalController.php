<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\JobValue\JobValueAssessmentCalculation;
use App\Model\HumanResource\JobValue\JobValueAssessment;
use App\Model\HumanResource\JobValue\JobValueAssessmentScore;
use App\Model\HumanResource\Employee\EmployeeAreaValue;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JobValueAssessmentApprovalController extends Controller
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
        $assessment = JobValueAssessment::findOrFail($id);
        $assessment = $assessment->load('employee');
        $assessment->approval_by = auth()->user()->id;
        $assessment->approval_at = now();
        $assessment->approval_status = 'approved';
        $assessment->save();

        return new ApiResource($assessment);
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
        $calculation = JobValueAssessment::findOrFail($id);
        $calculation->approval_by = auth()->user()->id;
        $calculation->approval_at = now();
        $calculation->approval_status = 'rejected';
        $calculation->save();

        return new ApiResource($calculation);
    }
}
