<?php

namespace App\Imports\Kpi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;

class TemplateIndicatorImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $kpiTemplateGroupId = \Session::get('kpiTemplateGroupId');
        $i = 1;
        $templateIndicatorId = [];

        foreach ($rows as $row) {
            if ($i > 1) {
                $kpiTemplateIndicator = new KpiTemplateIndicator;
                foreach ($kpiTemplateGroupId as $datas) {
                    foreach ($datas as $old => $new) {
                        if ($old == $row[1]) {
                            $kpiTemplateIndicator->kpi_template_group_id = $new;
                        }
                    }
                }
                $kpiTemplateIndicator->name = $row[2];
                if ($row[3] == null) {
                    $kpiTemplateIndicator->weight = 0;
                } else {
                    $kpiTemplateIndicator->weight = $row[3];
                }
                if ($row[4] == null) {
                    $kpiTemplateIndicator->target = 0;
                } else {
                    $kpiTemplateIndicator->target = $row[4];
                }
                $kpiTemplateIndicator->save();

                array_push($templateIndicatorId, [
                    $row[0] => $kpiTemplateIndicator->id,
                ]);
            }
            $i = $i + 1;
        }
        \Session::put('kpiTemplateIndicatorId', $templateIndicatorId);
    }
}
