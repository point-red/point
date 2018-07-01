<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupCollection;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource;
use App\Http\Requests\HumanResource\Kpi\KpiTemplateGroup\StoreKpiTemplateGroupRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplateGroup\UpdateKpiTemplateGroupRequest;

class KpiTemplateGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiTemplateGroupCollection(KpiTemplateGroup::where('kpi_template_id', $request->get('kpi_template_id'))->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplateGroup\StoreKpiTemplateGroupRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource
     */
    public function store(StoreKpiTemplateGroupRequest $request)
    {
        $kpiTemplateGroup = new KpiTemplateGroup();
        $kpiTemplateGroup->kpi_template_id = $request->input('kpi_template_id');
        $kpiTemplateGroup->name = $request->input('name');
        $kpiTemplateGroup->save();

        return new KpiTemplateGroupResource($kpiTemplateGroup);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource
     */
    public function show($id)
    {
        return new KpiTemplateGroupResource(KpiTemplateGroup::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplateGroup\UpdateKpiTemplateGroupRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource
     */
    public function update(UpdateKpiTemplateGroupRequest $request, $id)
    {
        $kpiTemplateGroup = KpiTemplateGroup::findOrFail($id);
        $kpiTemplateGroup->kpi_template_id = $request->input('kpi_template_id');
        $kpiTemplateGroup->name = $request->input('name');
        $kpiTemplateGroup->save();

        return new KpiTemplateGroupResource($kpiTemplateGroup);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateGroup\KpiTemplateGroupResource
     */
    public function destroy($id)
    {
        $kpiTemplateGroup = KpiTemplateGroup::findOrFail($id);

        $kpiTemplateGroup->delete();

        return new KpiTemplateGroupResource($kpiTemplateGroup);
    }
}
