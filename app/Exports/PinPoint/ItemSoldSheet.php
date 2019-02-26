<?php

namespace App\Exports\PinPoint;

use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class ItemSoldSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
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
        return SalesVisitationDetail::query()
            ->join(SalesVisitation::getTableName(),SalesVisitation::getTableName() . '.id', '=', SalesVisitationDetail::getTableName() . '.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName() . '.form_id')
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
            ->select(SalesVisitationDetail::getTableName().'.*')
            ->addSelect(SalesVisitation::getTableName().'.name as customerName')
            ->addSelect(SalesVisitation::getTableName().'.payment_method as paymentMethod')
            ->addSelect(SalesVisitation::getTableName().'.due_date as dueDate');
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
            'Item',
            'Quantity',
            'Price',
            'Total',
            'Payment Method',
            'Due Date',
            'Repeat',
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
            $row->salesVisitation->form->createdBy->first_name . ' ' . $row->salesVisitation->form->createdBy->last_name,
            $row->customerName,
            $row->item->name,
            $row->quantity,
            $row->price,
            $row->quantity * $row->price,
            $row->paymentMethod,
            $row->dueDate != '0000-00-00' ? date('Y-m-d', strtotime($row->dueDate)) : '',
            $row->salesVisitation->is_repeat_order == 1 ? 'Repeat' : '',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Item Sold';
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
