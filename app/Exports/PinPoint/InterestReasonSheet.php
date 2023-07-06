<?php

namespace App\Exports\PinPoint;

use App\Model\CloudStorage;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class InterestReasonSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize, ShouldQueue
{
    public $timeout = 180;
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct($userId, string $dateFrom, string $dateTo, $branchId, $cloudStorageId)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
        $this->branchId = $branchId;
        $this->cloudStorageId = $cloudStorageId;
        $this->userId = $userId;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        if ($this->branchId) {
            return SalesVisitationInterestReason::query()
            ->join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationInterestReason::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->where(SalesVisitation::getTableName('branch_id'), '=', $this->branchId)
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
            ->select(SalesVisitationInterestReason::getTableName().'.*')
            ->addSelect(SalesVisitation::getTableName().'.name as customerName');
        }

        return SalesVisitationInterestReason::query()
            ->join(SalesVisitation::getTableName(), SalesVisitation::getTableName().'.id', '=', SalesVisitationInterestReason::getTableName().'.sales_visitation_id')
            ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
            ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
            ->whereIn(SalesVisitation::getTableName('branch_id'), tenant($this->userId)->branches->pluck('id'))
            ->select(SalesVisitationInterestReason::getTableName().'.*');
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
            'Customer Code',
            'Customer Name',
            'Interest Reason',
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
            $row->salesVisitation->form->createdBy->first_name.' '.$row->salesVisitation->form->createdBy->last_name,
            $row->salesVisitation->customer->code,
            $row->salesVisitation->customer->name,
            $row->name,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Interest Reason';
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function (BeforeExport $event) {
                $event->writer->setCreator('Point');
            },
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:F1')->getFont()->setBold(true);
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                ];
                $event->getSheet()->getStyle('A1:F100')->applyFromArray($styleArray);
                if($this->cloudStorageId) {
                    $cloudStorage = CloudStorage::find($this->cloudStorageId);
                    $cloudStorage->percentage = $cloudStorage->percentage + 20;
                    $cloudStorage->save();
                }
            },
        ];
    }
}
