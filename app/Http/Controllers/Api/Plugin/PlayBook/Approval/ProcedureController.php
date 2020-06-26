<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Approval;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Mail\Plugin\PlayBook\Approval\ProcedureApprovalRequestSent;
use App\Model\Master\User;
use App\Model\Plugin\PlayBook\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Procedure::with('approver')
            ->filter($request)->orderBy('code');

        if ($request->want === 'send') {
            $query->approvalNotSent();
        } else {
            $query->approvalRequested();
        }

        $procedures = pagination($query, $request->limit ?: 10);

        return new ApiCollection($procedures);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        $request->validate([
            'approver_id' => ['required', 'numeric'],
        ]);

        Procedure::approvalNotSent()->whereIn('id', $request->ids)->update([
            'approval_request_by' => $request->user()->id,
            'approval_request_at' => now(),
            'approval_request_to' => $request->approver_id,
        ]);

        $procedures = Procedure::whereIn('id', $request->ids)->get();

        $approver = User::findOrFail($request->approver_id);

        foreach ($procedures as $procedure) {
            Mail::to([
                $approver->email,
            ])->queue(new ProcedureApprovalRequestSent(
                $procedure,
                $approver,
                $_SERVER['HTTP_REFERER']
            ));
        }

        return [
            'input' => $request->all(),
        ];
    }

    /**
     * Approve a procedure.
     */
    public function approve(Procedure $procedure)
    {
        if ($procedure->approval_action === 'store') {
            $procedure->update([
                'approved_at' => now(),
            ]);
        } elseif ($procedure->approval_action === 'update') {
            $source = Procedure::findOrFail($procedure->procedure_pending_id);
            $source->update($procedure->toArray());
            $source->update([
                'approved_at' => now(),
            ]);
            $source->duplicateToHistory();
            $procedure->delete();

            return $source;
        } elseif ($procedure->approval_action === 'destroy') {
            $source = Procedure::findOrFail($procedure->procedure_pending_id);
            $source->delete();
            Procedure::whereProcedurePendingId($procedure->procedure_pending_id)->delete();
            $procedure->delete();
        }

        return $procedure;
    }

    /**
     * Decline an approval request.
     */
    public function decline(Request $request, Procedure $procedure)
    {
        $procedure->update([
            'approval_note' => $request->approval_note,
            'declined_at' => now(),
        ]);

        return $procedure;
    }
}
