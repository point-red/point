<?php

namespace App\Model;

class Form extends PointModel
{
    protected $connection = 'tenant';

    protected $fillable = [];

    /**
     * The approvals that belong to the form.
     */
    public function approval()
    {
        return $this->hasMany(FormApproval::class);
    }

    /**
     * Get all of the owning formable models.
     */
    public function formable()
    {
        return $this->morphTo();
    }
}
