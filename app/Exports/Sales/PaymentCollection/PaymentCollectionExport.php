<?php

namespace App\Exports\Sales\PaymentCollection;

use App\Model\Sales\PaymentCollection\PaymentCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class PaymentCollectionExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
        $paymentCollections = PaymentCollection::join('forms', 'forms.formable_id', '=', PaymentCollection::getTableName().'.id')
            ->where('forms.formable_type', PaymentCollection::$morphName)
            ->whereIn(PaymentCollection::getTableName().'.id', $this->ids)
            ->select('date', 'number', 'customer_name', 'payment_type', 'amount')
            ->selectRaw("(CASE WHEN approval_status = 0 THEN 'Pending' WHEN approval_status = -1 THEN 'Rejected' ELSE 'Approved' END) AS approval_status")
            ->selectRaw("(CASE WHEN cancellation_status = 1 THEN 'Canceled' WHEN done = 1 THEN 'Approved' ELSE 'Pending' END) AS form_status")
            ->orderBy('date', 'desc');
        return $paymentCollections;
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
            ['Payment Collection'],
            [$this->tenantName],
            [
            'Date Form',
            'Form Number',
            'Customer',
            'Payment Method',
            'Amount Collection',
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
            $row->customer_name,
            strtoupper($row->payment_type),
            (int)$row->amount,
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
                $title = 'A4:G4'; // All headers
                $event->sheet->mergeCells($title);
                $event->sheet->getDelegate()->getStyle($title)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($title)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $tenanName = 'A3:G3'; // All headers
                $event->sheet->mergeCells($tenanName);
                $event->sheet->getDelegate()->getStyle($tenanName)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanName)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            },

        ];
    }     
}
