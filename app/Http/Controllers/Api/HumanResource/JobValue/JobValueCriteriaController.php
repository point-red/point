<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\JobValue\JobValueCriteria\StoreJobValueCriteriaRequest;
use App\Http\Requests\HumanResource\JobValue\JobValueCriteria\UpdateJobValueCriteriaRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\JobValue\JobValueCriteria;
use App\Model\HumanResource\JobValue\JobValueCriteriaScale;
use App\Model\HumanResource\JobValue\JobValueCategory;
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

        $criteria = $criteria->join(JobValueCategory::getTableName().' as category',
                'category.id', '=', 'job_value_criterias.category_id');

        $criteria = pagination($criteria, $request->get('limit'));

        return new ApiCollection($criteria);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function categories()
    {
        $categories = JobValueCriteria::pluck('category')
            ->unique()
            ->map(function ($category) {
                return [
                    'id'    => $category,
                    'label' => $category,
                ];
            });

        return new ApiResource($categories);
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
            $criteria->category_id = $request->input('category_id');
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
            ->with('scales')
            ->with('category')
            ->first();

        return new ApiResource($criteria);
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
        $criteria->category_id = $request->input('category_id');
        $criteria->criteria_factor = $request->input('criteria_factor');
        $criteria->total_score = $request->input('total_score');
        $criteria->save();

        foreach ($request->input('scales') as $scale) {
            if (isset($scale['id'])) {
                // Update existing area value
                $scaleValue = JobValueCriteriaScale::find($scale['id']);
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