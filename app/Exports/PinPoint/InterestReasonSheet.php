<?php

namespace App\Exports\PinPoint;

use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class InterestReasonSheet implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return SalesVisitationInterestReason::query()
            ->join(SalesVisitation::getTableName(),SalesVisitation::getTableName() . '.id', '=', SalesVisitationInterestReason::getTableName() . '.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName() . '.form_id')
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
            ->select(SalesVisitationInterestReason::getTableName().'.*')
            ->addSelect(SalesVisitation::getTableName().'.name as customerName');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Date',
            'Time',
            'Customer',
            'Interest Reason',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('Y-m-d', strtotime($row->salesVisitation->form->date)),
            date('H:i', strtotime($row->salesVisitation->form->date)),
            $row->customerName,
            $row->name,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Interest Reason';
    }
}
