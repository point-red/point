<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\JobValue\JobValueAssessment\StoreJobValueAssessmentRequest;
use App\Http\Requests\HumanResource\JobValue\JobValueAssessment\UpdateJobValueAssessmentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\JobValue\JobValueAssessment;
use App\Model\HumanResource\JobValue\JobValueAssessmentCalculation;
use App\Model\HumanResource\JobValue\JobValueAssessmentScore;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class JobValueAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $assessment = JobValueAssessment::eloquentFilter($request)
            ->select('job_value_assessments.*');

        $assessment = pagination($assessment, $request->get('limit'));

        return new ApiCollection($assessment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueAssessment\StoreJobValueAssessmentRequest $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function store(StoreJobValueAssessmentRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $assessment = new JobValueAssessment();
            $assessment->employee_id = $request->input('employee_id');
            $assessment->period_from = $request->input('period_from');
            $assessment->period_to = $request->input('period_to');
            $assessment->status = $request->input('status');
            $assessment->approval_status = 'pending';
            $assessment->total_value = $request->input('total_value');
            $assessment->total_score = $request->input('total_score');
            $assessment->save();

            if ($request->has('scores')) {
                foreach ($request->scores as $scores) {
                    try {
                        $assessment->scores()->create($scores);
                    } catch (\Exception $e) {
                        \Log::error('Error saving scale:', ['error' => $e->getMessage(), 'scores' => $scores]);
                    }
                }
            }

            return new ApiResource($assessment->load('scores'));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show(Request $request, $id)
    {
        $assessment = JobValueAssessment::eloquentFilter($request)
            ->select('job_value_assessments.*')
            ->where('id', $id)
            ->first();

        return new ApiResource($assessment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueAssessment\UpdateJobValueAssessmentRequest $request
     * @param  int $id
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function update(UpdateJobValueAssessmentRequest $request, $id)
    {
        $assessment = JobValueAssessment::findOrFail($id);
        $assessment->employee_id = $request->input('employee_id');
        $assessment->period_from = $request->input('period_from');
        $assessment->period_to = $request->input('period_to');
        $assessment->status = $request->input('status');
        $assessment->approval_status = 'pending';
        $assessment->total_value = $request->input('total_value');
        $assessment->total_score = $request->input('total_score');
        $assessment->save();

        foreach ($request->input('scores') as $score) {
            if (isset($score['id'])) {
                // Update existing area value
                $scoreValue = JobValueAssessmentScore::find($score['id']);
                if ($scoreValue) {
                    $scoreValue->update([
                        'criteria_id' => $score['criteria_id'],
                        'score' => $score['score'],
                        'description' => $score['description'],
                        'value' => $score['value'],
                        'note' => $score['note'],
                    ]);
                }
            } else {
                // Create new area value
                $assessment->scores()->create([
                    'criteria_id' => $score['criteria_id'],
                    'score' => $score['score'],
                    'description' => $score['description'],
                    'value' => $score['value'],
                    'note' => $score['note'],
                ]);
            }
        }

        return new ApiResource($assessment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function destroy($id)
    {
        $assessment = JobValueAssessment::findOrFail($id);

        $assessment->delete();

        return response(null, 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function showCalculation(Request $request, $id)
    {
        $assessment = JobValueAssessmentCalculation::eloquentFilter($request)
            ->select('job_value_assessment_calculations.*')
            ->where('assessment_id', $id)
            ->first();

        return new ApiResource($assessment);
    }
}