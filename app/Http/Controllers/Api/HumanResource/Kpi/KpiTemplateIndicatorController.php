<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiTemplateIndicator\StoreKpiTemplateIndicatorRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplateIndicator\UpdateKpiTemplateIndicatorRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorCollection;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorResource;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Illuminate\Http\Request;

class KpiTemplateIndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiTemplateIndicatorCollection(KpiTemplateIndicator::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplateIndicator\StoreKpiTemplateIndicatorRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorResource
     */
    public function store(StoreKpiTemplateIndicatorRequest $request)
    {
        $kpiTemplateIndicator = new KpiTemplateIndicator();
        $kpiTemplateIndicator->kpi_template_group_id = $request->input('kpi_template_group_id');
        $kpiTemplateIndicator->name = $request->input('name');
        $kpiTemplateIndicator->weight = $request->input('weight');
        $kpiTemplateIndicator->target = $request->input('target');
        $kpiTemplateIndicator->save();

        return new KpiTemplateIndicatorResource($kpiTemplateIndicator);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorResource
     */
    public function show($id)
    {
        return new KpiTemplateIndicatorResource(KpiTemplateIndicator::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplateIndicator\UpdateKpiTemplateIndicatorRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateIndicator\KpiTemplateIndicatorResource
     */
    public function update(UpdateKpiTemplateIndicatorRequest $request, $id)
    {
        $kpiTemplateIndicator = KpiTemplateIndicator::findOrFail($id);
        $kpiTemplateIndicator->kpi_template_group_id = $request->input('kpi_template_group_id');
        $kpiTemplateIndicator->name = $request->input('name');
        $kpiTemplateIndicator->weight = $request->input('weight');
        $kpiTemplateIndicator->target = $request->input('target');
        $kpiTemplateIndicator->save();

        return new KpiTemplateIndicatorResource($kpiTemplateIndicator);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiTemplateIndicator::findOrFail($id)->delete();

        return response(null, 204);
    }
}
