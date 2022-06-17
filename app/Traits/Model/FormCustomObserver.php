<?php

namespace App\Traits\Model;

trait FormCustomObserver
{
    // this is list of custom event to observe on App\Observers\FormObserver
    protected $_events = [
        'requestApproval',
        'approved', 
        'approvedByEmail', 
        'rejected', 
        'rejectedByEmail', 
        'canceled', 
        'cancelApproved', 
        'cancelRejected',
        'closed', 
        'closeApproved', 
        'closeRejected'
    ];

    public function bindObservables()
    {
        $this->observables = array_merge($this->observables, $this->_events);
    }

    public function fireEventRequestApproval()
    {
        $this->fireModelEvent('requestApproval');
    }
    public function fireEventApproved()
    {
        $this->fireModelEvent('approved');
    }
    public function fireEventApprovedByEmail()
    {
        $this->fireModelEvent('approvedByEmail');
    }
    public function fireEventRejected()
    {
        $this->fireModelEvent('rejected');
    }
    public function fireEventRejectedByEmail()
    {
        $this->fireModelEvent('rejectedByEmail');
    }

    public function fireEventCanceled()
    {
        $this->fireModelEvent('canceled');
    }
    public function fireEventCancelApproved()
    {
        $this->fireModelEvent('cancelApproved');
    }
    public function fireEventCancelRejected()
    {
        $this->fireModelEvent('cancelRejected');
    }

    public function fireEventClosed()
    {
        $this->fireModelEvent('closed');
    }
    public function fireEventCloseApproved()
    {
        $this->fireModelEvent('closeApproved');
    }
    public function fireEventCloseRejected()
    {
        $this->fireModelEvent('closeRejected');
    }
}
