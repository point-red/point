<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\JobValue\JobValueCriteria\StoreJobValueCriteriaRequest;
use App\Http\Requests\HumanResource\JobValue\JobValueCriteria\UpdateJobValueCriteriaRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\JobValue\JobValueCriteria;
use App\Model\HumanResource\JobValue\JobValueCriteriaScale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class JobValueCriteriaController extends Controller
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
        $criteria = JobValueCriteria::eloquentFilter($request)
            ->select('job_value_criterias.*');

        $criteria = pagination($criteria, $request->get('limit'));

        return new ApiCollection($criteria);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueCriteria\StoreJobValueCriteriaRequest $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function store(StoreJobValueCriteriaRequest $request)
    {
        \Log::info('Incoming Request:', $request->all());
        return DB::connection('tenant')->transaction(function () use ($request) {
            $criteria = new JobValueCriteria();
            $criteria->category = $request->input('category');
            $criteria->criteria_factor = $request->input('criteria_factor');
            $criteria->total_score = $request->input('total_score');
            $criteria->save();

            \Log::debug('Saved Criteria:', ['id' => $criteria->id]);


            if ($request->has('scales')) {
                foreach ($request->scales as $scale) {
                    try {
                        $criteria->scales()->create($scale);
                        \Log::debug('Scale saved:', $scale);  // Log saved scale
                    } catch (\Exception $e) {
                        \Log::error('Error saving scale:', ['error' => $e->getMessage(), 'scale' => $scale]);
                    }
                }
            }

            return new ApiResource($criteria->load('scales'));
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function show($id)
    {
        $criteria = JobValueCriteria::select('job_value_criterias.*')
            ->where('job_value_criterias.id', $id)
            ->first();

        return new ApiResource($criteria->load('scales'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\JobValueCriteria\UpdateJobValueCriteriaRequest $request
     * @param  int $id
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function update(UpdateJobValueCriteriaRequest $request, $id)
    {
        $criteria = JobValueCriteria::findOrFail($id);
        $criteria->category = $request->input('category');
        $criteria->criteria_factor = $request->input('criteria_factor');
        $criteria->total_score = $request->input('total_score');
        $criteria->save();

        foreach ($request->input('scales') as $scale) {
            if (isset($scale['id'])) {
                // Update existing area value
                $scaleValue = JobValueCriteria::find($scale['id']);
                if ($scaleValue) {
                    $scaleValue->update([
                        'description' => $scale['description'],
                        'value' => $scale['value'],
                    ]);
                }
            } else {
                // Create new area value
                $criteria->scales()->create([
                    'description' => $scale['description'],
                        'value' => $scale['value'],
                ]);
            }
        }

        return new ApiResource($criteria->load('scales'));
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
        $criteria = JobValueCriteria::findOrFail($id);

        $criteria->delete();

        return response(null, 204);
    }
}