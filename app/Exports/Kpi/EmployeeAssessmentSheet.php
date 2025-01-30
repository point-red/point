<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\Kpi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssessmentAssessmentSheet implements FromCollection, WithTitle, WithHeadings
{
    private $employee_id;
    private $dateFrom;
    private $dateTo;    

    public function __construct(int $employee_id, string $dateFrom, string $dateTo)
    {
        $this->employee_id = $employee_id;
        $this->dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d H:i:s', strtotime($dateTo));
    }

    public function collection()
    {
        // return KpiTemplate::where('id', $this->id)->get();
        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'));

        $kpis = $kpis->where('employee_id', $employee_id)
        ->where('date', '>=',$dateFrom)
        ->where('date', '<=',$dateTo)
        ->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc');

        $kpis = pagination($kpis, 1000);

        return $kpis;
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
