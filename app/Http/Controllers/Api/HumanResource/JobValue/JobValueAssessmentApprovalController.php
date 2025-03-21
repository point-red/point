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

        $previousAssessment = JobValueAssessment::where('period_from', '<', $assessment->period_from)
            ->orderBy('period_from', 'desc')
            ->first();

        $calculation = new JobValueAssessmentCalculation();
        $calculation->assessment_id = $assessment->id;
        if ($previousAssessment) {
            $calculation->prev_assessment_id = $previousAssessment->id;
            $calculation->score_diff = $assessment->total_score -  $previousAssessment->total_score;
            $calculation->score_diff_pct = ($calculation->score_diff / $previousAssessment->total_score) * 100;
        } else {
            $calculation->prev_assessment_id = null;
            $calculation->score_diff = $assessment->total_score;
            $calculation->score_diff_pct = 100;
        }

        $date = Carbon::parse($assessment->period_from);
        $currentYear = $date->format('Y');
        $areaValue = EmployeeAreaValue::where('job_location_id', $assessment->employee->employee_job_location_id)
            ->where('year', $currentYear)
            ->first();

        if ($areaValue) {
            $calculation->area_value_id = $areaValue->id;
            $calculation->area_value = $areaValue->value;
        } else {
            $calculation->area_value_id = null;
            $calculation->area_value = 0;
        }        

        $prevAreaValue = EmployeeAreaValue::where('job_location_id', $assessment->employee->employee_job_location_id)
            ->where('year', $currentYear-1)
            ->first();

        if ($prevAreaValue) {
            $calculation->prev_area_value_id = $prevAreaValue->id;
            $calculation->prev_area_value = $prevAreaValue->value;
        } else {
            $calculation->prev_area_value_id = null;
            $calculation->prev_area_value = 0;
        }

        $calculation->area_value_diff = $calculation->area_value - $calculation->prev_area_value;
        $calculation->area_value_pct = ($calculation->area_value_diff / $calculation->prev_area_value) * 100;

        $calculation->save();

        return new ApiResource($calculation);
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
