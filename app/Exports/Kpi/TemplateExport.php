<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateExport implements FromCollection, WithTitle, WithHeadings
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        return KpiTemplate::where('id', $this->id)->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ];
    }

    public function title(): string
    {
        return 'Kpi Template';
    }
}
