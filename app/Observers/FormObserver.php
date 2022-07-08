<?php

namespace App\Observers;

use App\Model\Form;
use App\Model\UserActivity;

class FormObserver
{
    protected $modelNotToObserves = [];

    public function __construct()
    {
        $this->modelNotToObserves[] = \App\Model\Finance\CashAdvance\CashAdvance::$morphName;
    }

    // Handle custom form event's
    public function requestApproval(Form $form)
    {
        $activity = 'Request Approval';
        $this->_storeActivity($form, $activity);
    }
    public function approved(Form $form)
    {
        $activity = 'Approved';
        $this->_storeActivity($form, $activity);
    }
    public function approvedByEmail(Form $form)
    {
        $activity = 'Approved by Email';
        $this->_storeActivity($form, $activity);
    }
    public function rejected(Form $form)
    {
        $activity = 'Rejected';
        $this->_storeActivity($form, $activity);
    }
    public function rejectedByEmail(Form $form)
    {
        $activity = 'Rejected by Email';
        $this->_storeActivity($form, $activity);
    }
    public function canceled(Form $form)
    {
        $activity = 'Canceled';
        $this->_storeActivity($form, $activity);
    }
    public function cancelApproved(Form $form)
    {
        $activity = 'Cancel Approved';
        $this->_storeActivity($form, $activity);
    }
    public function cancelRejected(Form $form)
    {
        $activity = 'Cancel Rejected';
        $this->_storeActivity($form, $activity);
    }
    public function closed(Form $form)
    {
        $activity = 'Close';
        $this->_storeActivity($form, $activity);
    }
    public function closeApproved(Form $form)
    {
        $activity = 'Close Approved';
        $this->_storeActivity($form, $activity);
    }
    public function closeRejected(Form $form)
    {
        $activity = 'Close Rejected';
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
        if (in_array($form->formable_type, $this->modelNotToObserves)) return;

        // log update form
        $formNumberExist = Form::where('edited_number', $form->number)->first();
        if ($formNumberExist) {
            $form->edited_number = $formNumberExist->edited_number;
            $this->_updated($form);
            return;
        }

        $activity = 'Created';
        $this->_storeActivity($form, $activity);
    }

    /**
     * Handle the form "updated" logic not triggered in event.
     *
     * @param  \App\Model\Form  $form
     * @return void
     */
    public function _updated(Form $form)
    {
        $activity = 'Update';

        $userActivity = UserActivity::where('number', $form->edited_number)
            ->where('activity', 'like', '%' . $activity . '%');
                
        $updatedTo = $userActivity->count() + 1;
        
        $form->number = $form->edited_number;
        $activity = $activity . ' - ' . $updatedTo;
        $this->_storeActivity($form, $activity);
    }

    protected function _storeActivity($form, $activity)
    {
        UserActivity::create([
            'table_type' => $form->formable_type ?? Form::$morphName,
            'table_id' => $form->formable_id ?? $form->id,
            'number' => $form->number,
            'date' => now(),
            'user_id' => auth()->user()->id,
            'activity' => $activity
        ]);
    }
}
