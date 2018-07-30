<?php

namespace App\Helpers\Ratio;

class RateOfReturnInvestment extends Ratio implements RatioContract
{
    private $description = 'rasio untuk mengukur kemampuan asset untuk menghasilkan pendapatan bersih (semakin tinggi semakin baik)';

    public function get($dateFrom, $dateTo)
    {
        $date = $dateFrom;

        $months = $this->getTotalMonth($dateFrom, $dateTo);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, $this->getLabel($date));

            $value = $this->getRatio($this->getTotalNetProfit($date), $this->getTotal(array_merge($this->currentAssets, $this->otherAssets), $date));
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
