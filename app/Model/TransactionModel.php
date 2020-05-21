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
        $this->form->request_cancellation_to = $this->form->request_approval_to;
        $this->form->request_cancellation_at = now();
        $this->form->request_cancellation_reason = $request->get('request_cancellation_reason');
        $this->form->cancellation_status = 0;
        $this->form->save();
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
