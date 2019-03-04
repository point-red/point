<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateScoreExport implements FromCollection, WithTitle, WithHeadings
{

    private $id;

    public function __construct(int $id)
    {
        $this->id  = $id;
    }

    public function collection()
    {
        $kpiTemplate = KpiTemplate::where('id', $this->id)->with('Indicators')->first();
        $indicatorsId = [];
        foreach ($kpiTemplate->indicators as $key => $value) {
            array_push($indicatorsId, $value->id);
        }

        $templateScore = KpiTemplateScore::whereIn('kpi_template_indicator_id', $indicatorsId)->get();

        return $templateScore;
    }

    public function headings(): array
    {
        return [
            'id',
            'kpi_template_indicator_id',
            'description',
            'score',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at'
        ];
    }

    public function title(): string
    {
        return 'Kpi Template Score';
    }

}
