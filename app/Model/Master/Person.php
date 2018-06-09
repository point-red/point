<?php

namespace App\Model\Master;

use App\Model\HumanResource\Kpi\KpiPersonTemplate;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplatePerson;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $connection = 'tenant';

    protected $table = 'persons';

    public function category()
    {
        return $this->belongsTo('App\Model\Master\PersonCategory', 'person_category_id');
    }

    public function group()
    {
        return $this->belongsTo('App\Model\Master\PersonGroup', 'person_group_id');
    }

    public function kpiTemplate()
    {
        $kpiTemplatePerson = optional(KpiTemplatePerson::where('person_id', $this->id))->first();
        return KpiTemplate::find(optional($kpiTemplatePerson)->kpi_template_id);
    }
}
