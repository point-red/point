<?php

namespace App\Exports\Master;

use App\Model\Master\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class SupplierExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $tenantName, object $filters)
    {
        $this->tenantName = $tenantName;
        $this->filters = $filters;
    }

    public function query()
    {
        $suppliers = Supplier::from(Supplier::getTableName().' as '.Supplier::$alias)->eloquentFilter($this->filters);

        $suppliers = Supplier::joins($suppliers, $this->filters->get('join'));

        return Supplier::query();
    }

    public function headings(): array
    {
        $dateExport = Carbon::now()->timezone(config()->get('project.timezone'));
        $periodExport = $this->_getPeriodExport();
        
        return [
            ['Date Export', ': ' . $dateExport->format('d M Y H:i')],
            ['Period Export', ': ' . $periodExport],
            [$this->tenantName],
            ['Supplier'],
            [
                'Code',
                'Email',
                'Name',
                'Address',
                'Phone',
                'Bank Branch',
                'Bank Name',
                'Account Number',
                'Account Name',
                'Created At',
                'Updated At',
            ]
        ];
    }

    public function map($row): array
    {

        return [
            $row->code,
            $row->email,
            $row->name,
            $row->address,
            $row->phone,
            $row->bank_branch,
            $row->bank_name,
            $row->bank_account_number,
            $row->bank_account_name,
            date('d F Y', strtotime($row->created_at)),
            date('d F Y', strtotime($row->created_at)),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('F6:F100')
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
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
                $event->sheet->getStyle('A3:K4')->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                         ]);
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
