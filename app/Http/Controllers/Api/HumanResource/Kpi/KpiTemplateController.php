<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiTemplate\StoreKpiTemplateRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateCollection;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource;
use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Http\Request;

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

        return new KpiTemplateCollection(KpiTemplate::paginate($limit));
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiTemplate::findOrFail($id)->delete();

        return response(null, 204);
    }
}
