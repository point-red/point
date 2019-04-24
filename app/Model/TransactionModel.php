<?php

namespace App\Model;

use App\Traits\FormScopes;
use Illuminate\Http\Request;
use App\Exceptions\FormArchivedException;
use App\Exceptions\UpdatePeriodNotAllowedException;

class TransactionModel extends PointModel
{
    use FormScopes;

    public function requestCancel(Request $request)
    {
        if ($request->has('approver_id')) {
            // send request cancel
            $formCancellation = new FormCancellation;
            $formCancellation->requested_to = $request->approver_id;
            $formCancellation->requested_at = now();
            $formCancellation->requested_by = auth()->user()->id;
            $formCancellation->expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
            $formCancellation->token = substr(md5(now()), 0, 24);

            $this->form->cancellations()->save($formCancellation);

            return true;
        } else {
            // not request a cancellation form instead direct cancel
            $this->form->cancel();

            return false;
        }
    }

    public function updatedFormNotArchived()
    {
        if (is_null($this->form->number)) {
            throw new FormArchivedException();
        }
    }

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
