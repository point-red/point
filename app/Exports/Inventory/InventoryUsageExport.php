<?php

namespace App\Exports\Inventory;

use Carbon\Carbon;
use App\Model\Form;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use App\Model\Inventory\InventoryUsage\InventoryUsageItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InventoryUsageExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    /**
     * InventoryUsageExport constructor.
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
        $inventoryUsages = InventoryUsage::from(InventoryUsage::getTableName().' as '.InventoryUsage::$alias)
            ->eloquentFilter($this->filters);

        $inventoryUsages = InventoryUsage::joins($inventoryUsages, $this->filters->get('join'));

        return $inventoryUsages;
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER,
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
            ['Inventory Usage'],
            [
                'Date Form',
                'Form Number',
                'Warehouse',
                'Employee',
                'Account',
                'Item',
                'Production Number',
                'Expiry Date',
                'Quantity Usage',
                'Notes',
                'Allocation',
                'Notes',
                'Created By',
                'Approved By',
                'Approval Status',
                'Form Status',
                'Created At',
                'Updated At',
                'Deleted At'
            ]
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $form = Form::where('formable_id', $row->inventory_usage_id)
            ->with(['createdBy', 'approvalBy'])
            ->where('formable_type', InventoryUsage::$morphName)
            ->first();

        $usageItem = InventoryUsageItem::where('id', $row->id)
            ->with(['item', 'account', 'allocation'])
            ->first();

        return [
            date('d F Y', strtotime($form->date)),
            $form->number,
            $row->warehouse->name,
            $row->employee->name,
            $usageItem->account->name,
            $usageItem->item->name,
            $row->production_number,
            $row->expiry_date,
            round($row->quantity, 2) . ' ' . $row->unit,
            $row->notes,
            $usageItem->allocation->name,
            $form->notes,
            $form->createdBy->getFullNameAttribute(),
            optional($form->approvalBy)->getFullNameAttribute(),
            $form->approval_status,
            $form->done,
            date('d F Y', strtotime($form->created_at)),
            date('d F Y', strtotime($form->updated_at)),
            date('d F Y', strtotime($form->updated_at)),
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
