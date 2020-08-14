<?php

namespace App\Imports\Kpi;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class TemplateCheckImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        return $row;
    }
}
