<?php

namespace App\Traits\Model\Plugin\PinPoint;

use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationNoInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;

trait SalesVisitationRelation
{
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function interestReasons()
    {
        return $this->hasMany(SalesVisitationInterestReason::class);
    }

    public function noInterestReasons()
    {
        return $this->hasMany(SalesVisitationNoInterestReason::class);
    }

    public function similarProducts()
    {
        return $this->hasMany(SalesVisitationSimilarProduct::class);
    }

    public function details()
    {
        return $this->hasMany(SalesVisitationDetail::class);
    }
}
