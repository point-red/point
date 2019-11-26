<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\StoreKpiTemplateRequest;
use App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\HumanResource\Kpi\KpiTemplate\KpiTemplateResource;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @param StoreKpiTemplateRequest $request
     * @return KpiTemplateResource
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
        $template = KpiTemplate::with('groups.indicators.scores')
            ->select('kpi_templates.*')
            ->where('kpi_templates.id', $id)
            ->withCount(['indicators as target' => function ($query) {
                $query->select(DB::raw('sum(target)'));
            }])
            ->withCount(['indicators as weight' => function ($query) {
                $query->select(DB::raw('sum(weight)'));
            }])
            ->first();

        $template->target = (float) $template->target;

        return new ApiResource($template);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiTemplate\UpdateKpiTemplateRequest $request
     * @param  int                                                                      $id
     * @return KpiTemplateResource
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
     * @return KpiTemplateResource
     */
    public function destroy($id)
    {
        $kpiTemplate = KpiTemplate::findOrFail($id);

        $kpiTemplate->delete();

        return new KpiTemplateResource($kpiTemplate);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return KpiTemplateResource
     */
    public function duplicate(Request $request)
    {
        $kpiTemplate = KpiTemplate::find($request->input('kpi_template_id'));

        $newKpiTemplate = new KpiTemplate;
        $newKpiTemplate->name = $kpiTemplate->name . ' (duplicate)';
        $newKpiTemplate->save();

        foreach ($kpiTemplate->groups as $group) {
            $kpiTemplateGroup = new KpiTemplateGroup();
            $kpiTemplateGroup->kpi_template_id = $newKpiTemplate->id;
            $kpiTemplateGroup->name = $group->name;
            $kpiTemplateGroup->save();

            foreach ($group->indicators as $indicator) {
                $kpiTemplateIndicator = new KpiTemplateIndicator;
                $kpiTemplateIndicator->kpi_template_group_id = $kpiTemplateGroup->id;
                $kpiTemplateIndicator->name = $indicator->name;
                $kpiTemplateIndicator->weight = $indicator->weight;
                $kpiTemplateIndicator->target = $indicator->target;
                $kpiTemplateIndicator->automated_code = $indicator->automated_code;
                $kpiTemplateIndicator->save();

                foreach ($indicator->scores as $score) {
                    $kpiTemplateScore = new KpiTemplateScore();
                    $kpiTemplateScore->kpi_template_indicator_id = $kpiTemplateIndicator->id;
                    $kpiTemplateScore->description = $score->description;
                    $kpiTemplateScore->score = $score->score;
                    $kpiTemplateScore->save();
                }
            }
        }

        return new KpiTemplateResource($newKpiTemplate);
    }
}
