<?php

namespace App\Exports\Kpi;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
