<?php

namespace App\Model;

use App\Exceptions\FormArchivedException;
use App\Exceptions\UpdatePeriodNotAllowedException;
use App\Traits\DashboardChartPeriod;
use App\Traits\FormScopes;
use Illuminate\Http\Request;

class TransactionModel extends PointModel
{
    use FormScopes, DashboardChartPeriod;

    public function requestCancel(Request $request)
    {
        $canceled = false;

        if (tenant(auth()->user()->id)->id === $this->form->created_by) {
            // If auth user cancel his own form, then no need to approval
            // Should do any action on form canceled
            
            // $canceled = true;
        }

        $this->form->request_cancellation_to = $this->form->request_approval_to;
        $this->form->request_cancellation_by = tenant(auth()->user()->id)->id;
        $this->form->request_cancellation_at = now();
        $this->form->request_cancellation_reason = $request->get('reason');
        $this->form->cancellation_status = $canceled;
        $this->form->save();

        $this->form->fireEventCanceled();
    }

    public function requestClose(Request $request)
    {
        $close = false;

        if (tenant(auth()->user()->id)->id === $this->form->created_by) {
            // If auth user cancel his own form, then no need to approval
            // Should do any action on form canceled
            
            // $close = true;
        }

        $this->form->request_close_to = $this->form->request_approval_to;
        $this->form->request_close_by = tenant(auth()->user()->id)->id;
        $this->form->request_close_at = now();
        $this->form->request_close_reason = $request->get('reason');
        $this->form->close_status = $close;
        $this->form->save();

        $this->form->fireEventClosed();
    }

    /**
     * Cannot update form that already archived.
     *
     * @throws FormArchivedException
     */
    public function updatedFormNotArchived()
    {
        if (is_null($this->form->number)) {
            throw new FormArchivedException();
        }
    }

    /**
     * Cannot delete form that already deleted.
     *
     * @throws FormArchivedException
     */
    public function isNotCanceled()
    {
        if ($this->form->cancellation_status == 1) {
            throw new FormArchivedException();
        }
    }

    public function isActive()
    {
        return $this->form->number != null && $this->form->cancelation_status != 1;
    }

    public function isCancellationPending()
    {
        return $this->form->number != null && $this->cancelation_status == 0;
    }

    /**
     * Update form should be in same period.
     *
     * @param $date
     * @throws UpdatePeriodNotAllowedException
     */
    public function updatedFormInSamePeriod($date)
    {
        if (date('Y-m', strtotime($date)) != date('Y-m', strtotime($this->form->date))) {
            throw new UpdatePeriodNotAllowedException();
        }
    }

    public function archives()
    {
        return get_class($this)::join('forms', 'forms.formable_id', '=', $this::getTableName('id'))
            ->whereNotNull('edited_number')
            ->where('formable_type', $this->form->formable_type)
            ->where('increment', $this->form->increment)
            ->where('increment_group', $this->form->increment_group)
            ->select($this::getTableName('*'))
            ->with('form')
            ->get();
    }

    public function origin()
    {
        return get_class($this)::join('forms', 'forms.formable_id', '=', $this::getTableName('id'))
            ->whereNull('edited_number')
            ->where('formable_type', $this->form->formable_type)
            ->where('increment', $this->form->increment)
            ->where('increment_group', $this->form->increment_group)
            ->select($this::getTableName('*'))
            ->with('form')
            ->first();
    }
}
