<?php

namespace App\Model\Plugin\PinPoint;

use App\Model\Form;
use App\Model\PointModel;

class SalesVisitation extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'pin_point_sales_visitations';

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = convert_to_server_timezone($value);
    }

    public function getDueDateAttribute($value)
    {
        return convert_to_local_timezone($value);
    }

    public function form() {
        return $this->belongsTo(Form::class);
    }

    public function interestReasons() {
        return $this->hasMany(SalesVisitationInterestReason::class);
    }

    public function notInterestReasons() {
        return $this->hasMany(SalesVisitationNotInterestReason::class);
    }

    public function similarProducts() {
        return $this->hasMany(SalesVisitationSimilarProduct::class);
    }

    public function details() {
        return $this->hasMany(SalesVisitationDetail::class);
    }
}
