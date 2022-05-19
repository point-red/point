<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Form;

class FormChecklist extends PointModel
{
    protected $connection = 'tenant';

    protected $table = 'form_checklist';

    public static $alias = 'form_checklist';

    protected $fillable = ['number', 'feature', 'is_checked'];

    public function form()
    {
        return $this->belongsTo(Form::class, 'number', 'number');
    }

    public static function create($data)
    {
        $checklist = new Self;
        $checklist->number = $data['number'];
        $checklist->feature = $data['report_name'];
        $checklist->is_checked = $data['is_checked'];
        $checklist->save();
        
        return $checklist;
    }
}