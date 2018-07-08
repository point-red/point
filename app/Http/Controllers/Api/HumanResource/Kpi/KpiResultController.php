<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiResult;
use App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource;
use App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultCollection;
use App\Http\Requests\HumanResource\Kpi\KpiResult\StoreKpiResultRequest;
use App\Http\Requests\HumanResource\Kpi\KpiResult\UpdateKpiResultRequest;

class KpiResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultCollection
     */
    public function index()
    {
        return new KpiResultCollection(KpiResult::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiResult\StoreKpiResultRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource
     */
    public function store(StoreKpiResultRequest $request)
    {
        $kpiResult = new KpiResult();
        $kpiResult->score_min = $request->input('score_min');
        $kpiResult->score_max = $request->input('score_max');
        $kpiResult->criteria = $request->input('criteria');
        $kpiResult->notes = $request->input('notes');
        $kpiResult->save();

        return new KpiResultResource($kpiResult);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource
     */
    public function show($id)
    {
        return new KpiResultResource(KpiResult::findOrFail($id));
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource
     */
    public function showBy(Request $request)
    {
        if ($request->get('score_percentage')) {
            $scorePercentage = $request->get('score_percentage');

            $kpiResult = KpiResult::where('score_min', '<', $scorePercentage)
                ->where('score_max', '>', $scorePercentage)->first();

            if (! $kpiResult) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Kpi result not found',
                ], 422);
            }

            return new KpiResultResource($kpiResult);
        }

        return response()->json([
            'code' => 422,
            'message' => 'Kpi result not found',
        ], 422);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiResult\UpdateKpiResultRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource
     */
    public function update(UpdateKpiResultRequest $request, $id)
    {
        $kpiResult = KpiResult::findOrFail($id);
        $kpiResult->score_min = $request->input('score_min');
        $kpiResult->score_max = $request->input('score_max');
        $kpiResult->criteria = $request->input('criteria');
        $kpiResult->notes = $request->input('notes');
        $kpiResult->save();

        return new KpiResultResource($kpiResult);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource
     */
    public function destroy($id)
    {
        $kpiResult = KpiResult::findOrFail($id);

        $kpiResult->delete();

        return new KpiResultResource($kpiResult);
    }
}
