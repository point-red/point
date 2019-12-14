<?php

namespace App\Model;

class Media extends MasterModel {

    public static $morphName = 'Media';

    protected $connection = 'tenant';
}
