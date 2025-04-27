<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\StoreEmployeeJobLocationRequest;
use App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\UpdateEmployeeJobLocationRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use App\Model\HumanResource\Employee\EmployeeAreaValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmployeeJobLocationController extends Controller
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
        $job_locations = EmployeeJobLocation::eloquentFilter($request)
            ->select('employee_job_locations.*');

        $job_locations = pagination($job_locations, $request->get('limit'));

        return new ApiCollection($job_locations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\StoreEmployeeJobLocationRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function store(StoreEmployeeJobLocationRequest $request)
    {
        $employeeJobLocation = new EmployeeJobLocation();
        $employeeJobLocation->name = $request->input('name');
        $employeeJobLocation->base_salary = 0;
        $employeeJobLocation->multiplier_kpi = $request->input('multiplier_kpi');
        $employeeJobLocation->save();


        if ($request->has('area_values')) {
            foreach ($request->area_values as $area) {
                $employeeJobLocation->areaValues()->create($area);
            }
        }

        return new EmployeeJobLocationResource($employeeJobLocation->load('areaValues'));
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
        $employeeJobLocation = EmployeeJobLocation::select('employee_job_locations.*')
            ->where('employee_job_locations.id', $id)
            ->first();

        return new ApiResource($employeeJobLocation->load('areaValues'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\EmployeeJobLocation\UpdateEmployeeJobLocationRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function update(UpdateEmployeeJobLocationRequest $request, $id)
    {
        $employeeJobLocation = EmployeeJobLocation::findOrFail($id);
        $employeeJobLocation->name = $request->input('name');
        // $employeeJobLocation->base_salary = $request->input('base_salary');
        $employeeJobLocation->multiplier_kpi = $request->input('multiplier_kpi');
        $employeeJobLocation->save();

        foreach ($request->input('area_values') as $area) {
            if (isset($area['id'])) {
                // Update existing area value
                $areaValue = EmployeeAreaValue::find($area['id']);
                if ($areaValue) {
                    $areaValue->update([
                        'year' => $area['year'],
                        'value' => $area['value'],
                        'notes' => $area['notes'] ?? null,
                    ]);
                }
            } else {
                // Create new area value
                $employeeJobLocation->areaValues()->create([
                    'year' => $area['year'],
                    'value' => $area['value'],
                    'notes' => $area['notes'] ?? null,
                ]);
            }
        }

        return new EmployeeJobLocationResource($employeeJobLocation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Employee\EmployeeJobLocation\EmployeeJobLocationResource
     */
    public function destroy($id)
    {
        $employeeJobLocation = EmployeeJobLocation::with('areaValues.jobValue')->findOrFail($id);

        foreach ($employeeJobLocation->areaValues as $areaValue) {
            if ($areaValue->jobValue()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete Employee Job Location as some area values have associated job values.'
                ], 400);
            }
            if ($areaValue->prevJobValue()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete Employee Job Location as some area values have associated previous job values.'
                ], 400);
            }
        }

        $employeeJobLocation->delete();

        return response(null, 204);
    }
}
