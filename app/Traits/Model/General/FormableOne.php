<?php

namespace App\Traits\Model\General;

use App\Model\Form;

trait FormableOne
{
  public function form()
  {
    return $this->morphOne(Form::class, 'formable');
  }
}
