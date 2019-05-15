<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\StoreKpiTemplateRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest;

class KpiTemplateController extends Controller
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
        $templates = KpiTemplate::with('groups.indicators.scores')
            ->select('kpi_templates.*')
            ->withCount(['indicators as target' => function ($query) {
                $query->select(DB::raw('sum(target)'));
            }])
            ->withCount(['indicators as weight' => function ($query) {
                $query->select(DB::raw('sum(weight)'));
            }])
            ->paginate($request->input('limit') ?? 50);

        return new ApiCollection($templates);
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
     * @return \App\Http\Resources\ApiResource
     */
    public function show($id)
    {
        $templates = KpiTemplate::with('groups.indicators.scores')
            ->select('kpi_templates.*')
            ->where('kpi_templates.id', $id)
            ->withCount(['indicators as target' => function ($query) {
                $query->select(DB::raw('sum(target)'));
            }])
            ->withCount(['indicators as weight' => function ($query) {
                $query->select(DB::raw('sum(weight)'));
            }])
            ->first();

        $templates->target = (double)$templates->target;

        return new ApiResource($templates);
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
