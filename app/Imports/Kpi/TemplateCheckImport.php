<?php

namespace App\Imports\Kpi;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class TemplateCheckImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        return $row;
    }
}
