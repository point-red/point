<?php

namespace App\Imports\Kpi;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KpiTemplateImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Kpi Template' => new TemplateImport(),
            'Kpi Template Group' => new TemplateGroupImport(),
            'Kpi Template Indicator' => new TemplateIndicatorImport(),
            'Kpi Template Score' => new TemplateScoreImport(),
        ];
    }
}
