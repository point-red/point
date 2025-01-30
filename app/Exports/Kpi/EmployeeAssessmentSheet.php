<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\Kpi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiCollection;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource;

class EmployeeAssessmentSheet implements FromCollection, WithTitle, WithHeadings
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
        $kpis = $kpis->groupBy('kpis.id');
        $kpis = $kpis->where('employee_id', $this->employee_id)
        ->where('date', '>=',$this->dateFrom)
        ->where('date', '<=',$this->dateTo)
        ->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc')->get();

        $dates = [];
        $scores = [];

        foreach ($kpis as $key => $kpi) {
            array_push($dates, date('dMY', strtotime($kpi->date)));
            array_push($scores, number_format($kpi->score_percentage, 2));
        }

        \Log::info( (new KpiCollection($kpis))
        ->additional([
            'data_set' => [
                'dates' => $dates,
                'scores' => $scores,
            ],
        ]));

        return (new KpiCollection($kpis))
            ->additional([
                'data_set' => [
                    'dates' => $dates,
                    'scores' => $scores,
                ],
            ]);
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
