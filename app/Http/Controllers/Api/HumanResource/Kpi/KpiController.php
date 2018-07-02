<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\Kpi;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCollection;
use App\Http\Requests\HumanResource\Kpi\KpiCategory\StoreKpiRequest;
use App\Http\Requests\HumanResource\Kpi\KpiCategory\UpdateKpiRequest;

class KpiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        $kpis = Kpi::where('employee_id', $request->get('employee_id'))->paginate($limit);

        $dates = [];
        $scores = [];

        foreach ($kpis as $key => $kpi) {
            array_push( $dates, date('dMY', strtotime($kpi->date)));
            array_push( $scores, number_format($kpi->indicators->sum('score_percentage'), 2));
        }

        return (new KpiCollection($kpis))
            ->additional([
                'data_set' => [
                    'dates' => $dates,
                    'scores' => $scores
                ],
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiCategory\StoreKpiRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function store(StoreKpiRequest $request)
    {
        $kpi = new Kpi();
        $kpi->name = $request->input('name');
        $kpi->date = $request->input('date');
        $kpi->person_id = $request->input('person_id');
        $kpi->save();

        return new KpiResource($kpi);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function show($id)
    {
        return new KpiResource(Kpi::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiCategory\UpdateKpiRequest $request
     * @param  int                                                              $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function update(UpdateKpiRequest $request, $id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->name = $request->input('name');
        $kpi->date = $request->input('date');
        $kpi->person_id = $request->input('person_id');
        $kpi->save();

        return new KpiResource($kpi);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function destroy($id)
    {
        $kpi = Kpi::findOrFail($id);

        $kpi->delete();

        return new KpiResource($kpi);
    }
}
