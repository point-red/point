<?php

namespace App\Imports\Kpi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\HumanResource\Kpi\KpiTemplateScore;

class TemplateScoreImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $kpiTemplateIndicatorId = \Session::get('kpiTemplateIndicatorId');
        $i = 1;

        foreach ($rows as $row) {
            if ($i > 1) {
                $kpiTemplateScore = new kpiTemplateScore();
                foreach ($kpiTemplateIndicatorId as $datas) {
                    foreach ($datas as $old => $new) {
                        if ($old == $row[1]) {
                            $kpiTemplateScore->kpi_template_indicator_id = $new;
                        }
                    }
                }
                $kpiTemplateScore->description = $row[2];
                if ($row[3] == null) {
                    $kpiTemplateScore->score = 0;
                } else {
                    $kpiTemplateScore->score = $row[3];
                }
                $kpiTemplateScore->save();
            }
            $i = $i + 1;
        }
    }
}
