<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiResult\StoreKpiResultRequest;
use App\Http\Requests\HumanResource\Kpi\KpiResult\UpdateKpiResultRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultCollection;
use App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultResource;
use App\Model\HumanResource\Kpi\KpiResult;
use Illuminate\Http\Request;

class KpiResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiResultCollection(KpiResult::paginate($limit));
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiResult::findOrFail($id)->delete();

        return response(null, 204);
    }
}
