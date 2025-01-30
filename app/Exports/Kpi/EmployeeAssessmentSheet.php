<?php

namespace App\Exports\Kpi;

use App\Model\HumanResource\Kpi\Kpi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class EmployeeAssessmentSheet implements FromCollection, WithTitle, WithHeadings
{
    private $employee_id;
    private $employee_name;
    private $dateFrom;
    private $dateTo;    

    public function __construct(int $employee_id, string $employee_name, string $dateFrom, string $dateTo)
    {
        $this->employee_id = $employee_id;
        $this->employee_name = $employee_name;
        $this->dateFrom = date('Y-m-d H:i:s', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d H:i:s', strtotime($dateTo));
    }

    public function collection()
    {
        // return KpiTemplate::where('id', $this->id)->get();
        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->join('users', 'users.id', '=', 'kpis.scorer_id')
            ->select('kpis.date')
            ->addSelect('kpis.created_at')
            ->addSelect(DB::raw('CONCAT(users.first_name, " ", users.last_name) AS username'))
            ->addSelect('kpis.name')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect('kpis.status');
        
        $kpis = $kpis->where('employee_id', $this->employee_id)
            ->groupBy('kpis.id')
            ->where('kpis.date', '>=',$this->dateFrom)
            ->where('kpis.date', '<=',$this->dateTo)
            ->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc')->get();

        return $kpis;
    }

    public function headings(): array
    {
        return [
            'date',
            'created_at',
            'scorer',
            'kpi template',
            'weight',
            'max score',
            'score',
            'percentage',
            'status',
        ];
    }

    public function title(): string
    {
        $invalidCharacters = array('*', ':', '/', '\\', '?', '[', ']');
        return str_replace($invalidCharacters, '', $this->employee_name);
    }
}
