<?php

namespace App\Imports\Kpi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;

class TemplateGroupImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $i = 1;
        $templateGroupId = [];

        foreach ($rows as $row) {
            if ($i > 1) {
                $kpiTemplateGroup = new KpiTemplateGroup();
                $kpiTemplateGroup->kpi_template_id = \Session::get('kpiTemplateId');
                $kpiTemplateGroup->name = $row[2];
                $kpiTemplateGroup->save();

                array_push($templateGroupId, [
                    $row[0] => $kpiTemplateGroup->id,
                ]);
            }
            $i = $i + 1;
        }
        \Session::put('kpiTemplateGroupId', $templateGroupId);
    }
}
