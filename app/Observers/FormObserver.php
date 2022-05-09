<?php

namespace App\Observers;

use App\Model\Form;
use App\Model\UserActivity;
use Illuminate\Support\Facades\Log;

class FormObserver
{
    // Handle custom form event's
    public function approved(Form $form)
    {
        $activity = 'Approved';
        $this->_storeActivity($form, $activity);
    }
    public function rejected(Form $form)
    {
        $activity = 'Rejected';
        $this->_storeActivity($form, $activity);
    }

    /**
     * Handle the form "created" event.
     *
     * @param  \App\Model\Form  $form
     * @return void
     */
    public function created(Form $form)
    {
        $activity = 'Created';
        $this->_storeActivity($form, $activity);
    }

    protected function _storeActivity($form, $activity)
    {
        try {
            UserActivity::create([
                'table_type' => 'forms',
                'table_id' => $form->id,
                'number' => $form->number,
                'date' => now(),
                'user_id' => auth()->user()->id,
                'activity' => $activity
            ]);
        } catch (\Throwable $th) {
            Log::error('Failed to log UserActivity on Form model. reference_id: '.$form->id, $th);
        }
    }
}
