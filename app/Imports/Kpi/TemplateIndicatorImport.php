<?php

namespace App\Imports\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\DB;

class TemplateIndicatorImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $kpiTemplateGroupId = \Session::get('kpiTemplateGroupId');
        $i = 1;

        foreach ($rows as $row) {
            if ($i > 1) {
                $kpiTemplateIndicator = new KpiTemplateIndicator;
                foreach ($kpiTemplateGroupId as $datas) {
                    foreach ($datas as $old => $new) {
                        if ($old == $row[1]) {
                            $kpiTemplateIndicator->kpi_template_group_id = $new;
                            $test = $old;
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

                $templateIndicatorId[] = [
                  $row[0] => $kpiTemplateIndicator->id
                ];
            }
            $i = $i + 1;
        }
        \Session::put('kpiTemplateIndicatorId', $templateIndicatorId);
    }
}
