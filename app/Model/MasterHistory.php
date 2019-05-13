<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MasterHistory extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    /**
     * Get all of the owning historyable models.
     */
    public function historyable()
    {
        return $this->morphTo();
    }

    public function scopeWhereHistoryable($query, $class, $values)
    {
        if (! $class) {
            // TODO: throw exception
            return;
        }

        if (! $values) {
            // TODO: throw exception
            return;
        }

        $query->where(function ($q) use ($class, $values) {
            $q->where('historyable_type', $class)->whereIn('historyable_id', $values);
        });
    }

    public function scopeOrWhereHistoryable($query, $class, $values)
    {
        if (! $class) {
            // TODO: throw exception
            return;
        }

        if (! $values) {
            // TODO: throw exception
            return;
        }

        $query->orWhere(function ($q) use ($class, $values) {
            $q->where('historyable_type', $class)->whereIn('historyable_id', $values);
        });
    }
}
