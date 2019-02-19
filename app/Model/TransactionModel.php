<?php

namespace App\Model;

use App\Traits\FormScopes;
use Carbon\Carbon;

class TransactionModel extends PointModel
{
    use FormScopes;

    public function setEtaAttribute($value)
    {
        $this->attributes['eta'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }

    public function getEtaAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function setRequiredDateAttribute($value)
    {
        $this->attributes['required_date'] = Carbon::parse($value, request()->header('Timezone'))->timezone('UTC')->toDateTimeString();
    }

    public function getRequiredDateAttribute($value)
    {
        return Carbon::parse($value, 'UTC')->timezone(request()->header('Timezone'))->toDateTimeString();
    }

    public function edit($data)
    {
        $newTransaction = $this->create($data);
        $newId = $newTransaction->form->id;

        $this->archive($data['edited_notes'] ?? null, $newId);

        $this->updateAllEditedFormId($newId);

        return $newTransaction;
    }

    private function archive($notes, $newId)
    {
        $this->form->edited_number = $this->form->number;
        $this->form->number = null;
        $this->form->edited_notes = $notes;
        $this->form->edited_form_id = $newId;
        $this->form->save();
    }

    private function updateAllEditedFormId($newId)
    {
        $oldId = $this->form->id;
        Form::where('edited_form_id', $oldId)->update([
            'edited_form_id' => $newId,
        ]);
    }
}
