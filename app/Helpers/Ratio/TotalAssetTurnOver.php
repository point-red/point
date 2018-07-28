<?php

namespace App\Helpers\Ratio;

class TotalAssetTurnOver extends Ratio implements RatioContract
{
    private $description = 'rasio untuk mengukur tingkat perputaran total aktiva terhadap penjualan';

    public function get($dateFrom, $dateTo)
    {
        $date = $dateFrom;

        $months = $this->getTotalMonth($dateFrom, $dateTo);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, $this->getLabel($date));

            $value = $this->getRatio($this->getTotal($this->salesIncome, $date), $this->getTotal($this->assets, $date));
            array_push($values, $value);

            $date = $this->addOneMonth($date);
        }

        return response()->json([
            'data' => [
                'description' => $this->description,
                'result' => '',
                'labels' => $labels,
                'values' => $values,
            ],
        ]);
    }
}
