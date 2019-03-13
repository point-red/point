<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Kpi\TemplateExport;
use App\Exports\Kpi\TemplateGroupExport;
use App\Exports\Kpi\TemplateIndicatorExport;
use App\Exports\Kpi\TemplateScoreExport;

class KpiTemplateExport implements WithMultipleSheets
{
    use Exportable;

    protected $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new TemplateExport($this->id);
        $sheets[] = new TemplateGroupExport($this->id);
        $sheets[] = new TemplateIndicatorExport($this->id);
        $sheets[] = new TemplateScoreExport($this->id);

        return $sheets;
    }
}
