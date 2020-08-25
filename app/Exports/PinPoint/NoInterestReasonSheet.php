<?php

namespace App\Exports\PinPoint;

use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationNoInterestReason;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class NoInterestReasonSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
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
        return SalesVisitationNoInterestReason::query()
            ->join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationNoInterestReason::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
            ->select(SalesVisitationNoInterestReason::getTableName().'.*');
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
            'Customer Code',
            'Customer Name',
            'No Interest Reason',
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
            $row->salesVisitation->form->createdBy->first_name.' '.$row->salesVisitation->form->createdBy->last_name,
            $row->salesVisitation->customer->code,
            $row->salesVisitation->customer->name,
            $row->name,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'No Interest Reason';
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function (BeforeExport $event) {
                $event->writer->setCreator('Point');
            },
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFont()->setBold(true);
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                ];
                $event->getSheet()->getStyle('A1:E100')->applyFromArray($styleArray);
            },
        ];
    }
}
