<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MasterModel extends PointModel
{
    protected $user_logs = true;

    public function save(array $options = [])
    {
        $this->logUpdatedColumn();

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesUserLogs()) {
            $this->updateUserLog();
        }

        return parent::save();
    }

    public function archive()
    {
        $this->archived_at = Carbon::now();
        $this->archived_by = optional(auth()->user())->id;
        $this->save();
    }

    public function activate()
    {
        $this->archived_at = null;
        $this->archived_by = null;
        $this->save();
    }

    public function logUpdatedColumn()
    {
        if (! $this->exists) {
            return;
        }

        // Get class name of this object
        $class = get_class($this);

        // Get old data from this object
        $oldData = $class::findOrFail($this->id);

        // This ignored fields will not saved to the master histories
        $ignoreFields = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

        // Get all attributes of this object
        $attrs = array_keys($this->getAttributes());

        // Array of attributes that changed
        $array = [];

        foreach ($attrs as $attr) {
            if (! in_array($attr, $ignoreFields)) {
                if ($this->$attr != $oldData->$attr) {
                    $row = [];
                    $row['historyable_type'] = $class;
                    $row['historyable_id'] = $oldData->id;
                    $row['updated_by'] = optional(auth()->user())->id;
                    $row['column_name'] = $attr;
                    $row['old'] = $oldData->$attr;
                    $row['new'] = $this->$attr;

                    array_push($array, $row);
                }
            }
        }

        MasterHistory::insert($array);
    }

    /**
     * Get all of the master's histories.
     */
    public function histories()
    {
        return $this->morphMany(MasterHistory::class, 'historyable');
    }

    /**
     * Determine if the model uses logs.
     *
     * @return bool
     */
    public function usesUserLogs()
    {
        return $this->user_logs;
    }

    public function updateUserLog()
    {
        $this->updated_by = optional(auth()->user())->id;

        if (! $this->exists) {
            $this->created_by = optional(auth()->user())->id;
        }
    }
}
