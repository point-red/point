<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class CustomerGroup extends MasterModel
{
    protected $connection = 'tenant';

    protected $fillable = ['code', 'name'];
}
