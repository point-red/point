<?php

namespace App\Model\HumanResource\Kpi;

use Illuminate\Database\Eloquent\Model;

class KpiTemplatePerson extends Model
{
    protected $connection = 'tenant';

    protected $table = 'kpi_template_persons';
}
