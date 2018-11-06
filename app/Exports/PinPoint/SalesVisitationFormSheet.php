<?php

namespace App\Exports\PinPoint;

use App\Model\Plugin\PinPoint\SalesVisitation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesVisitationFormSheet implements FromQuery, WithHeadings, WithMapping, WithTitle
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
        return SalesVisitation::query()
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName() . '.form_id')
            ->with('form')
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo]);
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
            'Group',
            'Address',
            'Phone',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('Y-m-d', strtotime($row->form->date)),
            date('H:i', strtotime($row->form->date)),
            $row->name,
            $row->group,
            $row->address,
            $row->phone,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Sales Visitation Form';
    }
}
