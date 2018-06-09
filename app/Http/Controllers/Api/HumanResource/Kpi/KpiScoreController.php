<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiScore\StoreKpiScoreRequest;
use App\Http\Requests\HumanResource\Kpi\KpiScore\UpdateKpiScoreRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreCollection;
use App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreResource;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Kpi\KpiScoreDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiScoreCollection(KpiScore::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiScore\StoreKpiScoreRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreResource
     */
    public function store(StoreKpiScoreRequest $request)
    {
        $kpiScore = DB::transaction(function() use ($request) {
            $kpiScore = new KpiScore();
            $kpiScore->kpi_template_indicator_id = $request->input('kpi_template_indicator_id');
            $kpiScore->save();

            for ($i=0;$i<count($request->get('description'));$i++) {
                $kpiScoreDetail = new KpiScoreDetail();
                $kpiScoreDetail->kpi_score_id = $kpiScore->id;
                $kpiScoreDetail->description = $request->get('description')[$i];
                $kpiScoreDetail->score = $request->get('score')[$i];
                $kpiScoreDetail->save();
            }

            return $kpiScore;
        });

        return new KpiScoreResource($kpiScore);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreResource
     */
    public function show($id)
    {
        return new KpiScoreResource(KpiScore::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiScore\UpdateKpiScoreRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScore\KpiScoreResource
     */
    public function update(UpdateKpiScoreRequest $request, $id)
    {
        $kpiScore = DB::transaction(function() use ($request, $id) {
            $kpiScore = KpiScore::findOrFail($id);
            $kpiScore->kpi_template_indicator_id = $request->input('kpi_template_indicator_id');
            $kpiScore->save();

            // update kpi score detail
            for ($i=0;$i<count($request->get('description'));$i++) {
                $kpiScoreDetail = KpiScoreDetail::findOrFail($request->get('kpi_score_detail_id')[$i]);
                $kpiScoreDetail->kpi_score_id = $kpiScore->id;
                $kpiScoreDetail->description = $request->get('description')[$i];
                $kpiScoreDetail->score = $request->get('score')[$i];
                $kpiScoreDetail->save();
            }

            // remove kpi score detail
            KpiScoreDetail::whereNotIn('id', $request->get('kpi_score_detail_id'))->delete();

            return $kpiScore;
        });

        return new KpiScoreResource($kpiScore);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiScore::findOrFail($id)->delete();

        return response(null, 204);
    }
}
