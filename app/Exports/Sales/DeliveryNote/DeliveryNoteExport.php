<?php

namespace App\Exports\Sales\DeliveryNote;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DeliveryNoteExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    /**
     * DeliveryNoteExport constructor.
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
        $deliveryNotes = DeliveryNote::from(DeliveryNote::getTableName().' as '.DeliveryNote::$alias)
            ->eloquentFilter($this->filters);

        $deliveryNotes = DeliveryNote::joins($deliveryNotes, $this->filters->get('join'));

        return $deliveryNotes;
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
            ['Sales Delivery Notes'],
            [
                'Form Number',
                'Date Form',
                'Form Reference',
                'Warehouse',
                'Driver',
                'License Plat',
                'Customer',
                'Item',
                'Production Number',
                'Expiry Date',
                'Quantity Delivered',
                'Quantity Remaining',
                'Notes',
                'Created By',
                'Created At',
            ]
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $form = Form::where('formable_id', $row->delivery_note_id)
            ->where('formable_type', DeliveryNote::$morphName)
            ->first();

        return [
            $form->number,
            date('d F Y', strtotime($form->date)),
            $row->deliveryOrder->form->number,
            $row->warehouse->name,
            $row->driver,
            $row->license_plate,
            $row->customer->name,
            $row->item_name,
            $row->production_number,
            $row->expiry_date,
            round($row->quantity, 2) . ' ' . $row->unit,
            round($row->quantity_remaining, 2) . ' ' . $row->unit,
            $row->notes,
            $form->createdBy->getFullNameAttribute(),
            date('d F Y', strtotime($form->createdAt)),
        ];
    }
    
    /**
     * @return array
     */
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

                $tenanNameColl = 'A3:O3';
                $event->sheet->mergeCells($tenanNameColl);
                $event->sheet->getDelegate()->getStyle($tenanNameColl)
                    ->getFont()
                    ->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanNameColl)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $titleColl = 'A4:O4';
                $event->sheet->mergeCells($titleColl);
                $event->sheet->getDelegate()->getStyle($titleColl)
                    ->getFont()
                    ->setBold(true);
                $event->sheet->getDelegate()->getStyle($titleColl)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }

    /**
     * @return string
     */
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
