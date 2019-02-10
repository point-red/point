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

    // public function collection()
    // {
    //     return KpiTemplate::all();
    // }

    // public function __construct(string $id)
    // {
    //     $this->id = $id;
    // }
    //
    // public function query()
    // {
    //     return KpiTemplate::query()->where('id', $this->id)->first();
    // }
    //
    // public function headings(): array
    // {
    //     return [
    //         'Name',
    //         'Created By',
    //         'Updated By',
    //     ];
    // }
    //
    // public function map($row): array
    // {
    //     return [
    //         $row->name,
    //         $row->created_by,
    //         $row->updated_by,
    //     ];
    // }
}
