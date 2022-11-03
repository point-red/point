<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\PointException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function approve(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        // ### Approve fail if
        // Jika Role Bukan Super Admin dan tidak memiliki akses approval maka mengirimkan pesan eror
        if ($request->has('token')) {
            // approve from email
            $approvalBy = $request->get('approver_id');
        } else {
            $payment->isHaveAccessToDelete();
            $approvalBy = auth()->user()->id;
        }
        $this->isCancellationRequestStillValid($payment);

        DB::connection('tenant')->transaction(function () use ($payment, $approvalBy) {
            // ### If approve success then
            // Status form cash out akan menjadi cancelled
            $payment->form->cancellation_approval_by = $approvalBy;
            $payment->form->cancellation_approval_at = now();
            $payment->form->cancellation_status = 1;

            // Status form payment order & cash advance jadi pending
            foreach ($payment->details as $paymentDetail) {
                $paymentDetail->referenceable->form->done = 0;
                $paymentDetail->referenceable->form->save();
            }

            $cashAdvanceAmount = $payment->details()->sum('amount') - $payment->amount;
            // Jumlah saldo cash account / cash advance/ biaya yang dipilih akan bertambah sebesar data yang dihapus (?)
            foreach ($payment->cashAdvances as $cashAdvance) {
                $cashAdvance->form->done = 0;
                $cashAdvance->form->save();
            }
            // $payment->disbursed = !$payment->disbursed;
            // Data jurnal cash out dari cash report / journal / general ledger/ subledger akan berkurang sebesar data yang dihapus (?)
            // $payment::updateJournal($payment);

            // Delete data allocation pada allocation report (?)

            $payment->form->save();
        });

        $payment->form = $payment->form;
        return new ApiResource($payment);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws ApprovalNotFoundException
     * @throws UnauthorizedException
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required'
        ]);

        $payment = Payment::findOrFail($id);

        // ### Reject fail if
        // Jika Role Bukan Super Admin / Pihak yang dipilih utk approval maka akan mengirimkan pesan eror 
        // Jika tidak memiliki akses approval pada payment order maka akan mengirimkan pesan eror
        if ($request->has('token')) {
            // reject from email
            $approvalBy = $request->get('approver_id');
        } else {
            $payment->isHaveAccessToDelete();
            $approvalBy = auth()->user()->id;
        }

        $this->isCancellationRequestStillValid($payment);

        DB::connection('tenant')->transaction(function () use ($payment, $request, $approvalBy) {
            // ### If reject success then
            // Update status approval form menjadi rejected
            $payment->form->approval_status = -1;

            // Update status form status menjadi  pending 
            $payment->form->done = 0;

            $payment->form->cancellation_approval_by = $approvalBy;
            $payment->form->cancellation_approval_at = now();
            $payment->form->cancellation_approval_reason = $request->get('reason');
            $payment->form->cancellation_status = -1;

            $payment->form->save();
        });

        $payment->form = $payment->form;
        return new ApiResource($payment);
    }

    public function isCancellationRequestStillValid($payment)
    {
        // is cancellation request already approved / rejected?
        $cancellationStatus = $payment->form->cancellation_status;
        if ($cancellationStatus == 1) {
            throw new PointException('Form cancellation already approved');
        }
        if ($cancellationStatus == -1) {
            throw new PointException('Form cancellation already rejected');
        }
    }
}
