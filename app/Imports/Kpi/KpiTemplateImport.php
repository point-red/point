<?php

namespace App\Imports\Kpi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use App\Imports\Kpi\TemplateImport;
use App\Imports\Kpi\TemplateGroupImport;
use App\Imports\Kpi\TemplateIndicatorImport;
use App\Imports\Kpi\TemplateScoreImport;

class KpiTemplateImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Kpi Template' => new TemplateImport(),
            'Kpi Template Group' => new TemplateGroupImport(),
            'Kpi Template Indicator' => new TemplateIndicatorImport(),
            'Kpi Template Score' => new TemplateScoreImport()
        ];
    }
}
