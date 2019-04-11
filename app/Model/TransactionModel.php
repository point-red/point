<?php

namespace App\Model;

use App\Exceptions\FormArchivedException;
use App\Exceptions\UpdatePeriodNotAllowedException;
use App\Http\Resources\ApiResource;
use App\Traits\FormScopes;
use Illuminate\Http\Request;

class TransactionModel extends PointModel
{
    use FormScopes;

    public function requestCancel(Request $request)
    {
        if ($request->has('approver_id')) {
            $formCancellation = new FormCancellation;
            $formCancellation->requested_to = $request->approver_id;
            $formCancellation->requested_at = now();
            $formCancellation->requested_by = auth()->user()->id;
            $formCancellation->expired_at = date('Y-m-d H:i:s', strtotime('+7 days'));
            $formCancellation->token = substr(md5(now()), 0, 24);

            $this->form->cancellations()->save($formCancellation);

            return new ApiResource($formCancellation);
        } else {
            $this->form->cancel();

            return response()->json([], 204);
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
        return Form::whereNotNull('edited_number')
            ->where('formable_type', $this->formable_type)
            ->where('increment', $this->increment)
            ->where('increment_group', $this->increment_group)
            ->get();
    }

    public function origin ()
    {
        return Form::whereNull('edited_number')
            ->where('formable_type', $this->formable_type)
            ->where('increment', $this->increment)
            ->where('increment_group', $this->increment_group)
            ->get();
    }
}
