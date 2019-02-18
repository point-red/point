<?php

namespace App\Imports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class TemplateCheckImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        // $kpiTemplate = KpiTemplate::where('name', $row['id'])->get();

        return $kpiTemplate;
    }
}
