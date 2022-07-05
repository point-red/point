<?php

namespace App\Model\Plugin\Study;

use App\Model\PointModel;

class StudySheet extends PointModel
{
    protected $connection = 'tenant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'started_at',
        'ended_at',
        'subject_id',
        'institution',
        'teacher',
        'competency',
        'learning_goals',
        'activities',
        'grade',
        'behavior',
        'remarks',
    ];

    /**
     * Get the study subject that referenced by this model
     *
     * @return eloquent
     */
    public function subject()
    {
        return $this->belongsTo(StudySubject::class, 'subject_id');
    }
}
