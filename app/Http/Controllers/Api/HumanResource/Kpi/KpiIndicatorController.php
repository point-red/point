<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiIndicator;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorResource;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorCollection;
use App\Http\Requests\HumanResource\Kpi\Kpi\StoreKpiIndicatorRequest;
use App\Http\Requests\HumanResource\Kpi\Kpi\UpdateKpiIndicatorRequest;

class KpiIndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiIndicatorCollection(KpiIndicator::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\Kpi\StoreKpiIndicatorRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorResource
     */
    public function store(StoreKpiIndicatorRequest $request)
    {
        $kpiIndicator = new KpiIndicator();
        $kpiIndicator->kpi_group_id = $request->input('kpi_group_id');
        $kpiIndicator->name = $request->input('name');
        $kpiIndicator->weight = $request->input('weight');
        $kpiIndicator->target = $request->input('target');
        $kpiIndicator->score = $request->input('score');
        $kpiIndicator->score_percentage = $request->input('score_percentage');
        $kpiIndicator->save();

        return new KpiIndicatorResource($kpiIndicator);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorResource
     */
    public function show($id)
    {
        return new KpiIndicatorResource(KpiIndicator::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\Kpi\UpdateKpiIndicatorRequest $request
     * @param  int                                                               $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiIndicatorResource
     */
    public function update(UpdateKpiIndicatorRequest $request, $id)
    {
        $kpiIndicator = KpiIndicator::findOrFail($id);
        $kpiIndicator->kpi_group_id = $request->input('kpi_group_id');
        $kpiIndicator->name = $request->input('name');
        $kpiIndicator->weight = $request->input('weight');
        $kpiIndicator->target = $request->input('target');
        $kpiIndicator->score = $request->input('score');
        $kpiIndicator->score_percentage = $request->input('score_percentage');
        $kpiIndicator->save();

        return new KpiIndicatorResource($kpiIndicator);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiIndicator::findOrFail($id)->delete();

        return response(null, 204);
    }
}
