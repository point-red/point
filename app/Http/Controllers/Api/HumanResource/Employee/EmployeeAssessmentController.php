<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCollection;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiResource;
use App\Model\HumanResource\Kpi\KpiIndicator;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        return new KpiCollection(Kpi::where('employee_id', $employeeId)->get());
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

        $kpiCategory = new Kpi;
        $kpiCategory->name = $template['name'];
        $kpiCategory->employee_id = $employeeId;
        $kpiCategory->save();

        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            $kpiGroup = new KpiGroup;
            $kpiGroup->kpi_category_id = $kpiCategory->id;
            $kpiGroup->name = $template['groups'][$groupIndex]['name'];
            $kpiGroup->save();

            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                $kpi = new KpiIndicator;
                $kpi->kpi_group_id = $kpiGroup->id;
                $kpi->indicator =  $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['name'];
                $kpi->weight = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['weight'];
                $kpi->target = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['target'];
                $kpi->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'];
                $kpi->score_percentage = $kpi->weight * $kpi->score / $kpi->target;
                $kpi->score_description = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['description'];
                $kpi->save();
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
    public function show($employee_id, $id)
    {
        return new KpiResource(Kpi::where('employee_id', $employee_id)->where('id', $id)->first());
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
