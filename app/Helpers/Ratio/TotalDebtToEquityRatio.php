<?php

namespace App\Helpers\Ratio;

class TotalDebtToEquityRatio extends Ratio implements RatioContract
{
    private $description = 'rasio untuk mengukur seberapa besar hutang perusahaan dibandingkan dengan modal';

    public function get($dateFrom, $dateTo) {
        $date = $dateFrom;

        $months = $this->getTotalMonth($dateFrom, $dateTo);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, $this->getLabel($date));

            $value = $this->getRatio($this->getTotal($this->liability, $date), $this->getTotal(['owner equity'], $date));
            array_push($values, $value);

            $date = $this->addOneMonth($date);
        }

        return response()->json([
            'data' => [
                'description' => $this->description,
                'result' => '',
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }
}
