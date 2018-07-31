<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiIndicator;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCollection;

class EmployeeAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $employeeId
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCollection
     */
    public function index($employeeId)
    {
        $kpis = Kpi::where('employee_id', $employeeId)->orderBy('date', 'asc')->get();

        $dates = [];
        $scores = [];

        foreach ($kpis as $key => $kpi) {
            array_push($dates, date('dMY', strtotime($kpi->date)));
            array_push($scores, number_format($kpi->indicators->sum('score_percentage'), 2));
        }

        return (new KpiCollection($kpis))
            ->additional([
                'data_set' => [
                    'dates' => $dates,
                    'scores' => $scores,
                ],
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param                           $employeeId
     *
     * @return void
     */
    public function store(Request $request, $employeeId)
    {
        $template = $request->get('template');

        DB::connection('tenant')->beginTransaction();

        $kpi = new Kpi;
        $kpi->name = $template['name'];
        $kpi->date = date('Y-m-d', strtotime($request->get('date')));
        $kpi->employee_id = $employeeId;
        $kpi->save();

        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            $kpiGroup = new KpiGroup;
            $kpiGroup->kpi_id = $kpi->id;
            $kpiGroup->name = $template['groups'][$groupIndex]['name'];
            $kpiGroup->save();

            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                $kpiIndicator = new KpiIndicator;
                $kpiIndicator->kpi_group_id = $kpiGroup->id;
                $kpiIndicator->name = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['name'];
                $kpiIndicator->weight = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['weight'];
                $kpiIndicator->target = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['target'];
                $kpiIndicator->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'];
                $kpiIndicator->score_percentage = $kpiIndicator->weight * $kpiIndicator->score / $kpiIndicator->target;
                $kpiIndicator->score_description = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['description'];
                $kpiIndicator->save();
            }
        }

        DB::connection('tenant')->commit();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $employee_id
     * @param  int $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function show($employeeId, $id)
    {
        return new KpiResource(Kpi::where('employee_id', $employeeId)->where('id', $id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource
     */
    public function destroy($employeeId, $id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->delete();

        return new KpiResource($kpi);
    }
}
