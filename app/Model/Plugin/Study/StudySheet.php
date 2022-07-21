<?php

namespace App\Model\Plugin\Study;

use App\Model\PointModel;
use App\Services\Google\Drive;

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
        'is_draft',
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

    /**
     * Get the preview link of photo.
     *
     * @param  string  $value
     * @return string
     */
    public function getPhotoAttribute()
    {
        if ($this->photo_file_id) {
            return Drive::previewUrl($this->photo_file_id);
        }
        return '';
    }

    /**
     * Get the preview link of audio.
     *
     * @param  string  $value
     * @return string
     */
    public function getAudioAttribute()
    {
        if ($this->audio_file_id) {
            return Drive::previewUrl($this->audio_file_id);
        }
        return '';
    }

    /**
     * Get the preview link of video.
     *
     * @param  string  $value
     * @return string
     */
    public function getVideoAttribute()
    {
        if ($this->video_file_id) {
            return Drive::previewUrl($this->video_file_id);
        }
        return '';
    }
}
