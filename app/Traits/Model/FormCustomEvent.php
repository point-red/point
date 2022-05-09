<?php

namespace App\Traits\Model;

trait FormCustomEvent
{
    // this is list of custom event to observe on App\Observers\FormObserver
    protected $customEvents = ['approved', 'rejected'];

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
}
