<?php

namespace App\Exports\TransferItem;

use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Inventory\TransferItem\ReceiveItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class ReceiveItemSendExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
        $receiveItems = ReceiveItem::join('forms as f1', 'f1.formable_id', '=', ReceiveItem::getTableName().'.id')
            ->where('f1.formable_type', ReceiveItem::$morphName)
            ->whereIn(ReceiveItem::getTableName().'.id', $this->ids)
            ->join('forms as f2', 'f2.formable_id', '=', ReceiveItem::getTableName().'.transfer_item_id')
            ->where('f2.formable_type', TransferItem::$morphName)
            ->join('warehouses as w1', 'w1.id', '=', ReceiveItem::getTableName().'.from_warehouse_id')
            ->join('warehouses as w2', 'w2.id', '=', ReceiveItem::getTableName().'.warehouse_id')
            ->join('receive_item_items as rii', 'rii.receive_item_id', '=', ReceiveItem::getTableName().'.id')
            ->select('f1.number as form_number', 'f1.date as date_receive', 'f2.number as form_reference', 'f2.date as date_send', 'w1.name as from_warehouse', 'w2.name as to_warehouse', 'rii.notes')
            ->addSelect('item_name', 'unit', 'production_number', 'expiry_date', 'quantity as quantity_receive')
            ->selectRaw("(SELECT COALESCE(quantity, 0) from transfer_item_items tii 
                where transfer_item_id = receive_items.transfer_item_id and item_id = rii.item_id 
                and COALESCE(expiry_date, '') = COALESCE(rii.expiry_date, '') and COALESCE(production_number, '') = COALESCE(rii.production_number, '')) as quantity_send")
            ->orderBy('f1.number', 'desc');
        return $receiveItems;
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
            ['Transfer Item Receive'],
            [
            'Form Reference',
            'Form Number',
            'From Warehouse',
            'To Warehouse',
            'Date Send',
            'Date Receive',
            'Item',
            'Production Number',
            'Expiry Date',
            'Quantity Send',
            'Quantity Receive',
            'Notes'
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
            $row->form_reference,
            $row->form_number,
            $row->from_warehouse,
            $row->to_warehouse,
            date('d F Y', strtotime($row->date_send)),
            date('d F Y', strtotime($row->date_receive)),
            $row->item_name,
            $row->production_number,
            date('d F Y', strtotime($row->expiry_date)),
            (int)$row->quantity_send . ' ' . $row->unit,
            (int)$row->quantity_receive . ' ' . $row->unit,
            $row->notes
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
                $tenanName = 'A3:L3'; // All headers
                $event->sheet->mergeCells($tenanName);
                $event->sheet->getDelegate()->getStyle($tenanName)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanName)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $title = 'A4:L4'; // All headers
                $event->sheet->mergeCells($title);
                $event->sheet->getDelegate()->getStyle($title)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($title)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },

        ];
    }     
}
