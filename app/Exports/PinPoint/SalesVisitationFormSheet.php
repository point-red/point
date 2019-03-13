<?php

namespace App\Exports\PinPoint;

use App\Model\Plugin\PinPoint\SalesVisitation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class SalesVisitationFormSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
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
            'Sales',
            'Customer',
            'Group',
            'Address',
            'Sub District',
            'District',
            'Latitude',
            'Longitude',
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
            $row->form->createdBy->first_name . ' ' . $row->form->createdBy->last_name,
            $row->name,
            $row->group,
            $row->address,
            $row->sub_district,
            $row->district,
            $row->latitude,
            $row->longitude,
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

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function(BeforeExport $event) {
                $event->writer->setCreator('Point');
            },
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setBold(true);
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                ];
                $event->getSheet()->getStyle('A1:K100')->applyFromArray($styleArray);
            },
        ];
    }
}
