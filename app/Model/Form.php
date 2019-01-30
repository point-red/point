<?php

namespace App\Model;

use App\Model\Master\User;

class Form extends PointModel
{
    protected $connection = 'tenant';

    protected $user_logs = true;

    protected $fillable = [];

    public function save(array $options = [])
    {
        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesUserLogs()) {
            $this->updateUserLog();
        }

        return parent::save();
    }

    /**
     * Determine if the model uses logs.
     *
     * @return bool
     */
    public function usesUserLogs($user_logs = null)
    {
        if (is_bool($user_logs)) {
          $this->user_logs = $user_logs;
        }

        return $this->user_logs;
    }

    public function updateUserLog()
    {
        $this->updated_by = optional(auth()->user())->id;

        if (!$this->exists) {
            $this->created_by = optional(auth()->user())->id;
        }
    }

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
