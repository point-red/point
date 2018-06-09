<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\Kpi\StoreKpiRequest;
use App\Http\Requests\HumanResource\Kpi\Kpi\UpdateKpiRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiCollection;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiCollection(Kpi::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\Kpi\StoreKpiRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource
     */
    public function store(StoreKpiRequest $request)
    {
        $kpi = new Kpi();
        $kpi->kpi_group_id = $request->input('kpi_group_id');
        $kpi->indicator = $request->input('indicator');
        $kpi->weight = $request->input('weight');
        $kpi->target = $request->input('target');
        $kpi->score = $request->input('score');
        $kpi->score_percentage = $request->input('score_percentage');
        $kpi->save();

        return new KpiResource($kpi);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource
     */
    public function show($id)
    {
        return new KpiResource(Kpi::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\Kpi\UpdateKpiRequest $request
     * @param  int                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource
     */
    public function update(UpdateKpiRequest $request, $id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->kpi_group_id = $request->input('kpi_group_id');
        $kpi->indicator = $request->input('indicator');
        $kpi->weight = $request->input('weight');
        $kpi->target = $request->input('target');
        $kpi->score = $request->input('score');
        $kpi->score_percentage = $request->input('score_percentage');
        $kpi->save();

        return new KpiResource($kpi);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Kpi::findOrFail($id)->delete();

        return response(null, 204);
    }
}
