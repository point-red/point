<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\KpiScoreDetail;
use App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailResource;
use App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailCollection;

class KpiScoreDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailCollection
     */
    public function index(Request $request)
    {
        $kpiScoreDetail = KpiScoreDetail::where('kpi_score_id', $request->get('kpi_score_id'))->orderBy('score')->get();

        return new KpiScoreDetailCollection($kpiScoreDetail);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailResource
     */
    public function store(Request $request)
    {
        $kpiScoreDetail = new KpiScoreDetail();
        $kpiScoreDetail->kpi_score_id = $request->get('kpi_score_id');
        $kpiScoreDetail->description = $request->get('description');
        $kpiScoreDetail->score = $request->get('score');
        $kpiScoreDetail->save();

        return new KpiScoreDetailResource($kpiScoreDetail);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailResource
     */
    public function update(Request $request, $id)
    {
        $kpiScoreDetail = KpiScoreDetail::findOrFail($id);
        $kpiScoreDetail->kpi_score_id = $request->get('kpi_score_id');
        $kpiScoreDetail->description = $request->get('description');
        $kpiScoreDetail->score = $request->get('score');
        $kpiScoreDetail->save();

        return new KpiScoreDetailResource($kpiScoreDetail);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiScoreDetail\KpiScoreDetailResource
     */
    public function destroy($id)
    {
        $kpiScoreDetail = KpiScoreDetail::findOrFail($id);

        $kpiScoreDetail->delete();
        info($kpiScoreDetail);

        return new KpiScoreDetailResource($kpiScoreDetail);
    }
}
