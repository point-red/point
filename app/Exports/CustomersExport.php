<?php

namespace App\Exports;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomersExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    private $customers;
    private $tenant_name;
    private $count_customers;
    private $datetimenow;

    public function __construct($customers, $tenant_name, $count_customers, $datetimenow)
    {
        $this->customers = $customers;
        $this->tenant_name = $tenant_name;
        $this->count_customers = $count_customers;
        $this->datetimenow = $datetimenow;
    }

    public function headings(): array
    {
        return [
            "No",
            "Customer Code",
            "Customer Name",
            "Email",
            "Phone",
            "Address",
            "Credit Limit",
            "Pricing Group",
            "Customer Group"
        ];
    }

    public function map($customers): array
    {
        $i = 1;
        return [
            $customers->increments,
            $customers->code,
            $customers->name,
            $customers->email,
            $customers->phone,
            $customers->address,
            $customers->credit_limit,
            $customers->pricing_group_label,
            $customers->groups
        ];
    }

    public function startCell(): string
    {
        return 'C5';
    }

    public function collection()
    {
        return $this->customers;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $datetime = new Datetime($this->datetimenow);
                $coordinate_border = "C5:K" . (5 + $this->count_customers);
                $event->sheet->getDelegate()->mergeCells("C4:K4");
                $event->sheet->getDelegate()->getColumnDimension('C')->setAutoSize(false);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(13);
                $event->sheet->getDelegate()->setCellValueExplicit("C4", $this->tenant_name, DataType::TYPE_STRING);
                $event->sheet->getDelegate()->getStyle("C4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->setCellValueExplicit("A2", "Date Export:", DataType::TYPE_STRING);
                $event->sheet->getDelegate()->setCellValue("B2", Date::dateTimeToExcel($datetime));
                $event->sheet->getDelegate()->getStyle("B2")->getNumberFormat()->setFormatCode("d MMMM yyyy h:mm");
                $event->sheet->getDelegate()->getStyle($coordinate_border)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
