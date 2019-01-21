<?php

namespace App\Model;

use App\Traits\FormScopes;

class TransactionModel extends PointModel
{
    use FormScopes;

    public function edit($data)
    {
        $data['number'] = $this->form->number;

        $this->archive($data['edited_notes'] ?? null);

        return $this->create($data);
    }

    private function archive($notes)
    {
        $this->form->edited_number = $this->form->number;
        $this->form->number = null;
        $this->form->edited_notes = $notes;
        $this->form->save();
    }
}
