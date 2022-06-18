<?php

namespace App\Exports\Sales\DeliveryOrder;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DeliveryOrderExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    /**
     * DeliveryOrderExport constructor.
     *
     * @param string $tenantName
     * @param object $filters
     */
    public function __construct(string $tenantName, object $filters)
    {
        $this->tenantName = $tenantName;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $deliveryOrders = DeliveryOrder::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)
            ->eloquentFilter($this->filters);

        $deliveryOrders = DeliveryOrder::joins($deliveryOrders, $this->filters->get('join'));

        return $deliveryOrders;
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
        $dateExport = Carbon::now()->timezone(config()->get('project.timezone'));
        $periodExport = $this->_getPeriodExport();
        
        return [
            ['Date Export', ': ' . $dateExport->format('d M Y H:i')],
            ['Period Export', ': ' . $periodExport],
            [$this->tenantName],
            ['Sales Delivery Order'],
            [
                'Date Form',
                'Form Number',
                'Form Reference',
                'Customer',
                'Warehouse',
                'Item',
                'Quantity Request',
                'Quantity Delivered',
                'Quantity Remaining',
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
        $form = Form::where('formable_id', $row->delivery_order_id)
            ->where('formable_type', DeliveryOrder::$morphName)
            ->first();

        return [
            date('d F Y', strtotime($form->date)),
            $form->number,
            $row->salesOrder->form->number,
            $row->customer->name,
            $row->warehouse->name,
            $row->item_name,
            round($row->quantity_requested, 2) . ' ' . $row->unit,
            round($row->quantity_delivered, 2) . ' ' . $row->unit,
            round($row->quantity_remaining, 2) . ' ' . $row->unit,
            $form->createdBy->getFullNameAttribute(),
            optional($form->approvalBy)->getFullNameAttribute(),
            $form->approval_status,
            $form->done
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
                $tenanNameColl = 'A3:M3'; // All headers
                $event->sheet->mergeCells($tenanNameColl);
                $event->sheet->getDelegate()->getStyle($tenanNameColl)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanNameColl)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $titleColl = 'A4:M4'; // All headers
                $event->sheet->mergeCells($titleColl);
                $event->sheet->getDelegate()->getStyle($titleColl)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($titleColl)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },

        ];
    }
    
    private function _getPeriodExport()
    {
        $dateMin = $this->filters->filter_date_min;
        $dateMin = !is_null($dateMin) && !is_array($dateMin)
            ? json_decode($dateMin, true)
            : [];
            
        $dateMax = $this->filters->filter_date_max;
        $dateMax = !is_null($dateMax) && !is_array($dateMax)
            ? json_decode($dateMax, true)
            : [];

        $periodExport = '';
        if (isset($dateMin['form.date'])) {
            $periodExport .= Carbon::createFromFormat('Y-m-d H:i:s', $dateMin['form.date'])->format('d M Y');
        }
        if (isset($dateMax['form.date'])) {
            if($periodExport !== '') $periodExport .= ' - ';
            $periodExport .= Carbon::createFromFormat('Y-m-d H:i:s', $dateMax['form.date'])->format('d M Y');
        }

        return $periodExport;
    }
}
