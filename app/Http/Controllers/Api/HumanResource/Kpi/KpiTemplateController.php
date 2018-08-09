<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateCollection;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\StoreKpiTemplateRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest;

class KpiTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiTemplateCollection(KpiTemplate::get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplate\StoreKpiTemplateRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource
     */
    public function store(StoreKpiTemplateRequest $request)
    {
        $kpiTemplate = new KpiTemplate();
        $kpiTemplate->name = $request->input('name');
        $kpiTemplate->save();

        return new KpiTemplateResource($kpiTemplate);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource
     */
    public function show($id)
    {
        return new KpiTemplateResource(KpiTemplate::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource
     */
    public function update(UpdateKpiTemplateRequest $request, $id)
    {
        $kpiTemplate = KpiTemplate::findOrFail($id);
        $kpiTemplate->name = $request->input('name');
        $kpiTemplate->save();

        return new KpiTemplateResource($kpiTemplate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource
     */
    public function destroy($id)
    {
        $kpiTemplate = KpiTemplate::findOrFail($id);

        $kpiTemplate->delete();

        return new KpiTemplateResource($kpiTemplate);
    }
}
