<?php

namespace App\Imports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TemplateImport implements ToCollection
{
    public function collection(Collection $rows)
    {
      $i = 1;
        foreach ($rows as $row) {
          if ($i > 1) {
              $kpiTemplate = new KpiTemplate();
              $kpiTemplate->name = $row[1];
              $kpiTemplate->save();
          }
          $i = $i + 1;
        }
        \Session::put('kpiTemplateId', $kpiTemplate->id);
    }
}
