<?php

namespace App\Exports\PinPoint;

use App\Model\CloudStorage;
use App\Model\Plugin\PinPoint\SalesVisitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class SalesVisitationFormSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize, ShouldQueue
{
    public $timeout = 180;
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct($userId, string $dateFrom, string $dateTo, $branchId, $cloudStorageId, $tenant)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
        $this->branchId = $branchId;
        $this->cloudStorageId = $cloudStorageId;
        $this->userId = $userId;
        $this->tenant = $tenant;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        if($this->tenant){
            config()->set('database.connections.tenant.database', env('DB_DATABASE', 'point').'_'.$this->tenant);
        }
        if ($this->branchId) {
            return SalesVisitation::query()
                ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
                ->where(SalesVisitation::getTableName('branch_id'), '=', $this->branchId)
                ->with('form')
                ->select(SalesVisitation::getTableName('*'))
                ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo]);
        }
        if(tenant($this->userId)->roles[0]->name == 'super admin') {
            return SalesVisitation::query()
                ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
                ->with('form')
                ->select(SalesVisitation::getTableName('*'))
                ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo]);
        } else {
            return SalesVisitation::query()
                ->join('forms', 'forms.id', '=', SalesVisitation::getTableName().'.form_id')
                ->whereIn(SalesVisitation::getTableName('branch_id'), tenant($this->userId)->branches->pluck('id'))
                ->with('form')
                ->select(SalesVisitation::getTableName('*'))
                ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo]);
        }
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Created At',
            'Date',
            'Time',
            'Sales',
            'Notes',
            'Customer Code',
            'Customer Name',
            'Group',
            'Address',
            'Sub District',
            'District',
            'Latitude',
            'Longitude',
            'Phone',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            date('Y-m-d H:i', strtotime($row->form->created_at)),
            date('Y-m-d', strtotime($row->form->date)),
            date('H:i', strtotime($row->form->date)),
            $row->form->createdBy->first_name.' '.$row->form->createdBy->last_name,
            $row->notes,
            $row->customer->code,
            $row->customer->name,
            $row->group,
            $row->address,
            $row->sub_district,
            $row->district,
            $row->latitude,
            $row->longitude,
            $row->phone,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Sales Visitation Form';
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
                $event->sheet->getDelegate()->getStyle('A1:N1')->getFont()->setBold(true);
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                ];
                $event->getSheet()->getStyle('A1:N100')->applyFromArray($styleArray);
                if($this->cloudStorageId) {
                    $cloudStorage = CloudStorage::find($this->cloudStorageId);
                    $cloudStorage->percentage = $cloudStorage->percentage + 20;
                    $cloudStorage->save();
                }
            },
        ];
    }
}
