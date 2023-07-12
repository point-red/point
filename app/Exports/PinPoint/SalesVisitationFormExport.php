<?php

namespace App\Exports\PinPoint;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesVisitationFormExport implements WithMultipleSheets
{
    /**
     * ScaleWeightItemExport constructor.
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function __construct(string $dateFrom, string $dateTo, $branchId)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
        $this->branchId = $branchId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new SalesVisitationFormSheet(auth()->user()->id, $this->dateFrom, $this->dateTo, $this->branchId, null, null);
        $sheets[] = new InterestReasonSheet(auth()->user()->id, $this->dateFrom, $this->dateTo, $this->branchId, null, null);
        $sheets[] = new NoInterestReasonSheet(auth()->user()->id, $this->dateFrom, $this->dateTo, $this->branchId, null, null);
        $sheets[] = new SimilarProductSheet(auth()->user()->id, $this->dateFrom, $this->dateTo, $this->branchId, null, null);
        $sheets[] = new ItemSoldSheet(auth()->user()->id, $this->dateFrom, $this->dateTo, $this->branchId, null, null);

        return $sheets;
    }
}
