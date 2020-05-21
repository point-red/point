<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateGroupExport implements FromCollection, WithTitle, WithHeadings
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        $kpiTemplate = KpiTemplate::where('id', $this->id)->with('Groups')->first();

        return $kpiTemplate->groups;
    }

    public function headings(): array
    {
        return [
            'id',
            'kpi_template_id',
            'name',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'total_target',
            'total_weight',
        ];
    }

    public function title(): string
    {
        return 'Kpi Template Group';
    }
}
