<?php

namespace App\Helpers\Ratio;

class InventoryTurnOver extends Ratio implements RatioContract
{
    private $description = 'rasio untuk mengukur tingkat efisiensi pengelolaan perputaran persediaan yang dimiliki terhadap penjualan. Semakin tinggi rasio ini akan semakin baik dan menunjukkan pengelolaan persediaan yang efisien.';

    public function get($dateFrom, $dateTo) {
        $date = $dateFrom;

        $months = $this->getTotalMonth($dateFrom, $dateTo);

        $labels = [];
        $values = [];
        for ($i = 0; $i < $months; $i++) {
            array_push($labels, $this->getLabel($date));

            $value = $this->getRatio($this->getTotal($this->salesIncome, $date), $this->getTotal(['inventory'], $date));
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
