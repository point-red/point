<?php

namespace App\Helpers\Ratio;

class CashRatio extends Ratio implements RatioContract
{
    private $description = 'rasio untuk mengukur kemampuan perusahaan dalam membayar kewajiban finansial jangka pendek dengan mengunakan kas dan setara kas yang tersedia, nilai ideal adalah 150%';

    public function get($dateFrom, $dateTo)
    {
        $date = $dateFrom;

        $months = $this->getTotalMonth($dateFrom, $dateTo);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, $this->getLabel($date));

            $value = $this->getRatio($this->getTotal($this->cashEquivalent, $date), $this->getTotal($this->currentLiability, $date));
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
