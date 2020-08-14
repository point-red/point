<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateIndicatorExport implements FromCollection, WithTitle, WithHeadings
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        $kpiTemplate = KpiTemplate::where('id', $this->id)->with('Indicators')->first();

        return $kpiTemplate->indicators;
    }

    public function headings(): array
    {
        return [
            'id',
            'kpi_template_group_id',
            'name',
            'weight',
            'target',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'kpi_template_id',
        ];
    }

    public function title(): string
    {
        return 'Kpi Template Indicator';
    }
}
