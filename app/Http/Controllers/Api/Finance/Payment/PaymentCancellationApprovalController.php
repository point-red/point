<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\PointException;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\Journal;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Finance\Payment\Payment;
use App\Model\UserActivity;
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
        $this->isCancellationRequestStillValid($payment);
        if ($request->has('token')) {
            // approve from email
            $approvalBy = $request->get('approver_id');
        } else {
            // Jika Role Bukan Super Admin dan tidak memiliki akses approval maka mengirimkan pesan eror
            $payment->isHaveAccessToDelete();
            $approvalBy = auth()->user()->id;
        }

        DB::connection('tenant')->transaction(function () use ($payment, $approvalBy) {
            // ### If approve success then
            // Status form cash out akan menjadi cancelled
            $payment->form->cancellation_approval_by = $approvalBy;
            $payment->form->cancellation_approval_at = now();
            $payment->form->cancellation_status = 1;
            $payment->form->save();

            // Jumlah saldo cash account / cash advance/ biaya yang dipilih akan bertambah sebesar data yang dihapus
            // Pengembalian dana cash advance & status formnya menjadi pending
            $amountPaidByCashAdvance = 0;
            if ($payment->cashAdvance) {
                $cashAdvancePayment = $payment->cashAdvance;
                $cashAdvance = $cashAdvancePayment->cashAdvance;
                $amountPaidByCashAdvance = $payment->cashAdvance->amount;

                $cashAdvance->amount_remaining += $amountPaidByCashAdvance;
                $cashAdvance->save();

                $cashAdvance->form->done = 0;
                $cashAdvance->form->save();

                $activity = 'Payment Refund (' . $payment->form->number . ')';
                $this->writeHistory($cashAdvance, $approvalBy, $activity);
            }

            // Pengembalian dana account
            $amountPaidByAccount = $payment->amount - $amountPaidByCashAdvance;
            $journal = new Journal;
            $journal->form_id = $payment->form->id;
            $journal->journalable_type = $payment->paymentable_type;
            $journal->journalable_id = $payment->paymentable_id;
            $journal->chart_of_account_id = $payment->payment_account_id;
            if ($payment->disbursed) {
                $journal->debit = $amountPaidByAccount;
            } else {
                $journal->credit = $amountPaidByAccount;
            }
            $journal->save();

            foreach ($payment->details as $paymentDetail) {
                $journal = new Journal;
                $journal->form_id = $payment->form->id;
                $journal->form_id_reference = optional(optional($paymentDetail->referenceable)->form)->id;
                $journal->journalable_type = $payment->paymentable_type;
                $journal->journalable_id = $payment->paymentable_id;
                $journal->notes = $paymentDetail->notes;
                $journal->chart_of_account_id = $paymentDetail->chart_of_account_id;
                if (!$payment->disbursed) {
                    $journal->credit = $paymentDetail->amount;
                } else {
                    $journal->debit = $paymentDetail->amount;
                }
                $journal->save();

                // Status form reference jadi pending
                $paymentDetail->referenceable->form->done = 0;
                $paymentDetail->referenceable->form->save();
            }

            // Delete data allocation pada allocation report
            $payment->allocationReports()->delete();
        });

        $payment->load('form');
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
        $payment = Payment::findOrFail($id);

        // ### Reject fail if
        $this->isCancellationRequestStillValid($payment);
        if ($request->has('token')) {
            // reject from email
            $approvalBy = $request->get('approver_id');
            $request->merge(['reason' => 'Rejected by email']);
        } else {
            $request->validate([
                'reason' => 'required'
            ]);
            // Jika Role Bukan Super Admin / Pihak yang dipilih utk approval maka akan mengirimkan pesan eror 
            // Jika tidak memiliki akses approval pada payment order maka akan mengirimkan pesan eror
            $payment->isHaveAccessToDelete();
            $approvalBy = auth()->user()->id;
        }

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
        // is cancellation request already approved?
        $cancellationStatus = $payment->form->cancellation_status;
        if ($cancellationStatus == 1) {
            throw new PointException('Form cancellation already approved');
        }
        // is cancellation request already rejected?
        // if ($cancellationStatus == -1) {
        //     throw new PointException('Form cancellation already rejected');
        // }
    }

    // $reference = cash advance,
    public function writeHistory($reference, int $userId, string $activity)
    {
        $history = new UserActivity;

        $history->table_type = $reference::$morphName;
        $history->table_id = $reference->id;
        $history->number = $reference->form->number;
        $history->user_id = $userId;
        $history->date = convert_to_local_timezone(date('Y-m-d H:i:s'));
        $history->activity = $activity;

        $history->save();
    }
}
