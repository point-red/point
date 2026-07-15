<?php

namespace App\Exports\Kpi;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Monthly KPI assessment export: one sheet per assessment template.
 * $templates is the array returned by EmployeeAssessmentController@showBy.
 */
class KpiMonthlyAssessmentExport implements WithMultipleSheets
{
    use Exportable;

    protected $templates;

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    public function sheets(): array
    {
        return array_map(fn ($template) => new KpiMonthlyAssessmentSheet($template), $this->templates);
    }
}
