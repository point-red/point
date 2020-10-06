<?php

namespace App\Http\Controllers\Api\Plugin\SalaryNonSales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\SalaryNonSales\EmployeeFeeRequest;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\SalaryNonSales\EmployeeFee;
use App\Model\Plugin\SalaryNonSales\EmployeeFeeCriteria;
use Illuminate\Http\Request;

class EmployeeFeeController extends Controller
{
    public function index(Request $request)
    {
        $employee_fees = new EmployeeFee;
        $employee_fees = EmployeeFee::where('employee_id', $request->employee_id)
        ->with('employee.jobLocation')
        ->with('factors');
        $employee_fees = pagination($employee_fees, $request->get('limit'));

        return new ApiCollection($employee_fees);
    }

    /**
     * Save or create employee fee
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(EmployeeFeeRequest $request)
    {
        $employee = new EmployeeFee;

        if ($id = $request->id) {
            $employee = EmployeeFee::findOrFail($request->id);
        }

        $criterias = collect($request->criterias);

        $employee->employee_id = $request->employee_id;
        $employee->fee = $request->fee;
        $employee->score = $criterias->sum('score');
        $employee->start_period = \Carbon\Carbon::parse($request->start_period);
        $employee->end_period = \Carbon\Carbon::parse($request->end_period);
        $employee->save();

        foreach ($criterias as $criteria) {
            $criteria = (object) $criteria;
            $criteria_model = new EmployeeFeeCriteria;
            $check = EmployeeFeeCriteria::where('employee_fee_id', $employee->id)->where('factor_id', $criteria->factor_id)->first();
            if ($check) {
                $criteria_model = $check;
            }
            $criteria_model->criteria_id = $criteria->criteria_id;
            $criteria_model->factor_id = $criteria->factor_id;
            $criteria_model->score = $criteria->score;
            $criteria_model->employee_fee_id = $employee->id;
            $criteria_model->save();
        }

        return response()->json([
            'message' => 'saved',
            'data' => $employee,
        ]);
    }
}
