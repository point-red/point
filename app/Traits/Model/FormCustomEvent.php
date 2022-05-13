<?php

namespace App\Traits\Model;

trait FormCustomEvent
{
    // this is list of custom event to observe on App\Observers\FormObserver
    protected $customEvents = ['approved', 'rejected', 'canceled', 'cancelApproved', 'cancelRejected'];

    public function bindObservables()
    {
        $this->observables = array_merge($this->observables, $this->customEvents);
    }

    public function fireEventApproved()
    {
        $this->fireModelEvent('approved');
    }

    public function fireEventRejected()
    {
        $this->fireModelEvent('rejected');
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
}
