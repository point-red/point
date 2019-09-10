<?php

namespace App\Exports\Salary;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class SalaryExport implements FromView, WithTitle, ShouldAutoSize
{
    /**
     * SalaryExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */ 
    public function __construct($employeeSalary, $additionalSalaryData, $calculatedSalaryData)
    {
        $this->employeeSalary = $employeeSalary;
        $this->additionalSalaryData = $additionalSalaryData;
        $this->calculatedSalaryData = $calculatedSalaryData;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Invoice';
    }


    public function view():view
    {
        return view('exports.human-resource.employee.salaryExcel', [
            'employeeSalary' => $this->employeeSalary,
            'additionalSalaryData' => $this->additionalSalaryData,
            'calculatedSalaryData' => $this->calculatedSalaryData
        ]);
    }
}
