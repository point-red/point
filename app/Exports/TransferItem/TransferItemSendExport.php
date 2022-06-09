<?php

namespace App\Exports\TransferItem;

use App\Model\Inventory\TransferItem\TransferItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class TransferItemSendExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $dateFrom, string $dateTo, array $ids, string $tenantName)
    {
        $this->dateFrom = date('d F Y', strtotime($dateFrom));
        $this->dateTo = date('d F Y', strtotime($dateTo));
        $this->ids = $ids;
        $this->tenantName = $tenantName;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $transferItems = TransferItem::join('forms', 'forms.formable_id', '=', TransferItem::getTableName().'.id')
            ->where('forms.formable_type', TransferItem::$morphName)
            ->whereIn(TransferItem::getTableName().'.id', $this->ids)
            ->join('warehouses as w1', 'w1.id', '=', TransferItem::getTableName().'.warehouse_id')
            ->join('warehouses as w2', 'w2.id', '=', TransferItem::getTableName().'.to_warehouse_id')
            ->join('users as u1', 'u1.id', '=', 'forms.created_by')
            ->leftJoin('users as u2', 'u2.id', '=', 'forms.approval_by')
            ->join('transfer_item_items as tii', 'tii.transfer_item_id', '=', TransferItem::getTableName().'.id')
            ->select('date', 'number', 'w1.name as warehouse_send', 'w2.name as warehouse_receive')
            ->addSelect('item_name', 'unit', 'production_number', 'expiry_date', 'quantity', 'balance')
            ->addSelect('u1.name as created_by', 'u2.name as approved_by')
            ->selectRaw("(CASE WHEN approval_status = 0 THEN 'Pending' WHEN approval_status = -1 THEN 'Rejected' ELSE 'Approved' END) AS approval_status")
            ->selectRaw("(CASE WHEN cancellation_status = 1 THEN 'Canceled' WHEN done = 1 THEN 'Approved' ELSE 'Pending' END) AS form_status")
            ->selectRaw("(SELECT COALESCE(quantity, 0) from receive_item_items rii 
                where receive_item_id = (SELECT ri.id from receive_items ri
                JOIN forms f on f.formable_id = ri.id and f.formable_type = 'ReceiveItem'
                where ri.transfer_item_id = transfer_items.id and f.number is not null and f.cancellation_status IS NOT TRUE and f.approval_status = 1) and item_id = tii.item_id 
                and COALESCE(expiry_date, '') = COALESCE(tii.expiry_date, '') and COALESCE(production_number, '') = COALESCE(tii.production_number, '')) as quantity_receive")
            ->orderBy('number', 'desc');
        return $transferItems;
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            ['Date Export', ': ' . date('d F Y', strtotime(Carbon::now()))],
            ['Period Export', ': ' . $this->dateFrom . ' - ' . $this->dateTo],
            [$this->tenantName],
            ['Transfer Item Send'],
            [
            'Date Form',
            'Form Number',
            'Warehouse Send',
            'Warehouse Receive',
            'Item',
            'Production Number',
            'Expiry Date',
            'Quantity Send',
            'Quantity Receive',
            'Created By',
            'Approved By',
            'Approval Status',
            'Form Status'
            ]
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('d F Y', strtotime($row->date)),
            $row->number,
            $row->warehouse_send,
            $row->warehouse_receive,
            $row->item_name,
            $row->production_number,
            date('d F Y', strtotime($row->expiry_date)),
            (int)$row->quantity . ' ' . $row->unit,
            (int)$row->quantity_receive . ' ' . $row->unit,
            $row->created_by,
            $row->approved_by,
            $row->approval_status,
            $row->form_status
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('F6:F100')
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $event->sheet->getColumnDimension('B')
                            ->setAutoSize(false)
                            ->setWidth(18);
                $tenanName = 'A3:M3'; // All headers
                $event->sheet->mergeCells($tenanName);
                $event->sheet->getDelegate()->getStyle($tenanName)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanName)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $title = 'A4:M4'; // All headers
                $event->sheet->mergeCells($title);
                $event->sheet->getDelegate()->getStyle($title)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($title)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },

        ];
    }     
}
