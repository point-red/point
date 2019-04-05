<?php

namespace App\Model;

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

            return response()->json($formCancellation, 201);
        } else {
            $this->form->cancel();

            return response()->json([], 204);
        }
    }
}
