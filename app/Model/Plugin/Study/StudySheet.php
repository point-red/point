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
     * The attributes that should be cast to native types.
     * https://laravel.com/docs/master/eloquent-mutators#attribute-casting.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
