<?php

namespace App\Helpers\Ratio;

class FixedAssetTurnOver extends Ratio implements RatioContract
{
    private $description = 'rasio ini berguna untuk mengevaluasi seberapa besar tingkat kemampuan perusahaan dalam memanfaatkan aktivatetap yang dimiliki secara efisien dalam rangka meningkatkan pendapatan';

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
