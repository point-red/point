<?php

namespace App\Model;

use App\Traits\FormScopes;

class TransactionModel extends PointModel
{
    use FormScopes;

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
