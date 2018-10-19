<?php

namespace App\Model;

use App\Traits\EloquentFilters;
use Illuminate\Database\Eloquent\Model;

class PointModel extends Model
{
    protected $user_logs = true;

    use EloquentFilters;

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
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public function usesUserLogs()
    {
        return $this->user_logs;
    }

    public function updateUserLog()
    {
        $this->updated_by = auth()->user()->id;

        if (!$this->exists) {
            $this->created_by = auth()->user()->id;
        }
    }
}
