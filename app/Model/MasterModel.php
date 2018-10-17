<?php

namespace App\Model;

class MasterModel extends PointModel
{
    public function save(array $options = [])
    {
        $this->logUpdatedColumn();

        return parent::save();
    }

    public function logUpdatedColumn()
    {
        if (!$this->exists) {
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
            if (!in_array($attr, $ignoreFields)) {
                if ($this->$attr != $oldData->$attr) {
                    $row = [];
                    $row['historyable_type'] = $class;
                    $row['historyable_id'] = $oldData->id;
                    $row['updated_by'] = auth()->user()->id;
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
        return $this->morphMany(get_class(new MasterHistory()), 'historyable');
    }
}
