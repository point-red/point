<?php

namespace App\Exports\Accounting;

use App\Model\Accounting\MemoJournal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class MemoJournalExport implements WithColumnFormatting, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
        $memoJournals = MemoJournal::join('forms', 'forms.formable_id', '=', MemoJournal::getTableName().'.id')
            ->where('forms.formable_type', MemoJournal::$morphName)
            ->whereIn(MemoJournal::getTableName().'.id', $this->ids)
            ->join('memo_journal_items as mji', 'mji.memo_journal_id', '=', MemoJournal::getTableName().'.id')
            ->leftJoin('forms as f', 'f.id', '=', 'mji.form_id')
            ->leftJoin('customers as c', 'c.id', '=', 'mji.masterable_id')
            ->leftJoin('employees as e', 'e.id', '=', 'mji.masterable_id')
            ->leftJoin('expeditions as ex', 'ex.id', '=', 'mji.masterable_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'mji.masterable_id')
            ->leftJoin('fixed_assets as fa', 'fa.id', '=', 'mji.masterable_id')
            ->leftJoin('items as i', 'i.id', '=', 'mji.masterable_id')
            ->select('forms.date', 'forms.number')
            ->addSelect('chart_of_account_name', 'f.number as reference', 'debit', 'credit')
            ->selectRaw("case when masterable_type = 'Customer' then c.name
                when masterable_type = 'Employee' then e.name
                when masterable_type = 'Expedition' then ex.name
                when masterable_type = 'Supplier' then s.name
                when masterable_type = 'FixedAsset' then fa.name
                when masterable_type = 'Item' then i.name
                else ''
                END AS master")
            ->orderBy('forms.number', 'desc');
        return $memoJournals;
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
            ['Memo Jurnal'],
            [
            'Date Form',
            'Form Number',
            'Account',
            'Master',
            'Reference',
            'Debit',
            'Credit'
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
            $row->chart_of_account_name,
            $row->master,
            $row->reference,
            $row->debit,
            $row->credit
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $last_row = $event->sheet->getHighestRow() + 1;
                $event->sheet->getColumnDimension('B')
                            ->setAutoSize(false)
                            ->setWidth(18);
                $tenanName = 'A3:G3'; // All headers
                $event->sheet->mergeCells($tenanName);
                $event->sheet->getDelegate()->getStyle($tenanName)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($tenanName)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $title = 'A4:G4'; // All headers
                $event->sheet->mergeCells($title);
                $event->sheet->getDelegate()->getStyle($title)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($title)
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $event->sheet->setCellValue(sprintf('A%d',$last_row),'Total');
                $event->sheet->mergeCells(sprintf('A%d',$last_row) . ':' . sprintf('E%d',$last_row));
                $event->sheet->getDelegate()->getStyle(sprintf('A%d',$last_row) . ':' . sprintf('G%d',$last_row))->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle(sprintf('A%d',$last_row) . ':' . sprintf('E%d',$last_row))
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->setCellValue(sprintf('F%d',$last_row),'=SUM(F6:' . sprintf('F%d',$last_row-1) . ')');
                $event->sheet->setCellValue(sprintf('G%d',$last_row),'=SUM(G6:' . sprintf('G%d',$last_row-1) . ')');
            },

        ];
    }     
}
