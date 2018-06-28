<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource;
use App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreCollection;

class KpiTemplateScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreCollection
     */
    public function index(Request $request)
    {
        $kpiTemplateScores = KpiTemplateScore::where('kpi_template_indicator_id', $request->get('kpi_template_indicator_id'))->orderBy('score')->get();

        return new KpiTemplateScoreCollection($kpiTemplateScores);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource
     */
    public function store(Request $request)
    {
        $kpiTemplateScore = new KpiTemplateScore();
        $kpiTemplateScore->kpi_template_indicator_id = $request->get('kpi_template_indicator_id');
        $kpiTemplateScore->description = $request->get('description');
        $kpiTemplateScore->score = $request->get('score');
        $kpiTemplateScore->save();

        return new KpiTemplateScoreResource($kpiTemplateScore);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource
     */
    public function show($id)
    {
        return new KpiTemplateScoreResource(KpiTemplateScore::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource
     */
    public function update(Request $request, $id)
    {
        $kpiTemplateScore = KpiTemplateScore::findOrFail($id);
        $kpiTemplateScore->kpi_template_indicator_id = $request->get('kpi_template_indicator_id');
        $kpiTemplateScore->description = $request->get('description');
        $kpiTemplateScore->score = $request->get('score');
        $kpiTemplateScore->save();

        return new KpiTemplateScoreResource($kpiTemplateScore);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiTemplateScore\KpiTemplateScoreResource
     */
    public function destroy($id)
    {
        $kpiTemplateScore = KpiTemplateScore::findOrFail($id);

        $kpiTemplateScore->delete();

        return new KpiTemplateScoreResource($kpiTemplateScore);
    }
}
