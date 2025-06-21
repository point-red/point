<?php

namespace App\Exports\Kpi;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Model\HumanResource\Employee\Employee;
use App\Exports\Kpi\EmployeeAssessmentSheet;

class EmployeeAssessmentExport implements WithMultipleSheets
{
    use Exportable;

    protected $employee_id;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($employee_id, string $dateFrom, string $dateTo)
    {
        $this->employee_id = $employee_id;
        $this->dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d H:i:s', strtotime($dateTo));
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        \Log::info('sheet');
        \Log::info($this->employee_id);
        if (!$this->employee_id) {
            $employees = Employee::all();
        } else {
            $employees = Employee::where('id', $this->employee_id)->get();
        }

        foreach ($employees as $employee) {
            $sheets[] = new EmployeeAssessmentSheet($employee->id, $employee->name, $this->dateFrom, $this->dateTo);
        }

        return $sheets;
    }
}
