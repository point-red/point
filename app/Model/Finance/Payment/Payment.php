<?php

namespace App\Model\Finance\Payment;

use App\Exceptions\BranchNullException;
use App\Exceptions\PointException;
use App\Exceptions\UnauthorizedException;
use App\Model\Accounting\Journal;
use App\Model\AllocationReport;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Form;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\TransactionModel;
use App\Traits\Model\Finance\PaymentJoin;
use App\Traits\Model\Finance\PaymentRelation;
use Carbon\Carbon;

class Payment extends TransactionModel
{
    use PaymentJoin, PaymentRelation;

    public static $morphName = 'Payment';

    protected $connection = 'tenant';

    public static $alias = 'payment';

    public $timestamps = false;

    protected $fillable = [
        'disbursed',
        'paymentable_type',
        'paymentable_id',
        'paymentable_name',
        'payment_account_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function isAllowedToUpdate()
    {
        // TODO isAllowed to update?
    }

    public function isAllowedToDelete()
    {
        // TODO isAllowed to delete?
        $form = $this->form;

        // Forbidden to delete,
        // - Jika tidak memiliki permission
        $this->isHaveAccessToDelete();

        // - Jika form sudah di-request to delete
        if ($form->request_cancellation_by != null) {
            throw new PointException("Form already request to delete");
        }

        // - Jika pada periode yang akan didelete sudah dilakukan close book maka akan mengirimkan pesan eror
        $now = Carbon::now();
        $formDate = Carbon::parse($form->date);
        if ($now->month != $formDate->month) {
            throw new PointException("Cannot delete form because the book period is closed");
        }
    }

    // Check if auth user have access to delete payment
    public function isHaveAccessToDelete()
    {
        $authUserId = auth()->user()->id;
        // Only super admin & approver referenceable can delete
        $isSuperAdmin = tenant($authUserId)->hasRole('super admin');
        $isApproverReferenceable = $this->details()->first()->referenceable->form->approval_by;
        if ((!$isSuperAdmin) && ($authUserId != $isApproverReferenceable)) {
            throw new UnauthorizedException();
        }
    }

    public static function create($data)
    {
        $payment = new self;
        $payment->fill($data);
        $payment->payment_type = strtoupper($payment->paymentAccount->type->name);
        $payment->paymentable_name = $data['paymentable_name'] ?? $payment->paymentable->name;

        $paymentDetails = self::mapPaymentDetails($data);

        // Reference Payment Order
        if (isset($data['referenceable_type']) && $data['referenceable_type'] == 'PaymentOrder') {
            $paymentOrder = PaymentOrder::find($data['referenceable_id']);
            if ($paymentOrder->payment_id != null) {
                throw new PointException();
            }
            $paymentOrder->payment_id = $payment->id;
            $paymentOrder->form->done = 1;
            $paymentOrder->form->save();
            $paymentOrder->save();
        }

        // Reference Down Payment
        if (isset($data['referenceable_type']) && $data['referenceable_type'] == 'PurchaseDownPayment') {
            $purchaseDownPayment = PurchaseDownPayment::find($data['referenceable_id']);
            if ($purchaseDownPayment->paid_by != null) {
                throw new PointException();
            }
            $purchaseDownPayment->remaining = $purchaseDownPayment->amount;
            $purchaseDownPayment->paid_by = $payment->id;
            $purchaseDownPayment->form->done = 1;
            $purchaseDownPayment->form->save();
            $purchaseDownPayment->save();
        }

        // Reference Sales Down Payment
        if (isset($data['referenceable_type']) && $data['referenceable_type'] == 'SalesDownPayment') {
            $salesDownPayment = SalesDownPayment::find($data['referenceable_id']);
            if ($salesDownPayment->paid_by != null) {
                throw new PointException();
            }
            $salesDownPayment->remaining = $salesDownPayment->amount;
            $salesDownPayment->paid_by = $payment->id;
            $salesDownPayment->form->done = 1;
            $salesDownPayment->form->save();
            $salesDownPayment->save();
        }

        // Reference Payment Collection
        $isPaymentCollection = false;
        $journalsPaymentCollection = [];
        if (isset($data['referenceable_type']) && $data['referenceable_type'] == 'PaymentCollection') {
            $paymentCollection = PaymentCollection::find($data['referenceable_id']);
            if ($paymentCollection->payment_id != null) {
                throw new PointException();
            }
            $paymentCollection->payment_id = $payment->id;
            $paymentCollection->form->done = 1;
            $paymentCollection->form->save();
            $paymentCollection->save();
            $isPaymentCollection = true;
            $journalsPaymentCollection = self::mapPaymentCollectionJournals($payment, $data);
        }

        if ($isPaymentCollection) {
            $payment->amount = $data['amount'] ?? $payment->amount;
        } else {
            $payment->amount = self::calculateAmount($paymentDetails);
        }

        $payment->save();

        $payment->details()->saveMany($paymentDetails);

        $form = new Form;
        $form->fill($data);
        $form->done = true;
        $form->approval_status = 1;

        $branches = tenant(auth()->user()->id)->branches;
        $form->branch_id = null;
        foreach ($branches as $branch) {
            if ($branch->pivot->is_default) {
                $form->branch_id = $branch->id;
                break;
            }
        }

        if ($form->branch_id == null) {
            throw new BranchNullException();
        }

        $form->formable_id = $payment->id;
        $form->formable_type = self::$morphName;
        $form->increment = self::getLastPaymentIncrement($payment, $data['increment_group']);
        $form->generateFormNumber(
            self::generateFormNumber($payment, $data['number'] ?? null, $form->increment),
            $data['paymentable_id'],
            $data['paymentable_id']
        );
        $form->save();

        if (isset($data['cashAdvances'])) {
            self::mapCashAdvances($data, $payment, $form);
        }

        // Save allocation reports
        self::mapAllocationReports($data, $payment);

        // Reference Cash Advance
        if (isset($data['referenceable_type']) && $data['referenceable_type'] == 'CashAdvance') {
            $cashAdvance = CashAdvance::find($data['referenceable_id']);
            if ($cashAdvance->payment_id != null || $cashAdvance->amount_remaining < $payment->amount) {
                throw new PointException('Amount is over pay');
            }
            $cashAdvance->payments()->attach($payment->id);
            $cashAdvance->amount_remaining = $cashAdvance->amount_remaining - $payment->amount;
            if ($cashAdvance->amount_remaining == 0) {
                $cashAdvance->form->done = 1;
                $cashAdvance->form->save();
            }
            $cashAdvance->save();

            $data['activity'] = ucfirst(strtolower($cashAdvance->payment_type)) . ' Out Withdrawal (' . $form->number . ')';
            CashAdvance::mapHistory($cashAdvance, $data);
        }

        self::updateReferenceDone($paymentDetails);
        if ($isPaymentCollection) {
            self::updateJournalPaymentCollection($payment, $journalsPaymentCollection);
        } else {
            // error_log('false');
            self::updateJournal($payment);
        }

        return $payment;
    }

    private static function mapPaymentDetails($data)
    {
        return array_map(function ($detail) use ($data) {
            $paymentDetail = new PaymentDetail;
            $paymentDetail->fill($detail);
            $paymentDetail->referenceable_type = $data['referenceable_type'] ?? null;
            $paymentDetail->referenceable_id = $data['referenceable_id'] ?? null;

            return $paymentDetail;
        }, $data['details']);
    }

    private static function mapCashAdvances($data, $payment, $form)
    {
        return array_map(function ($detail) use ($data, $payment, $form) {
            if ($payment->amount == 0) {
                return;
            }
            // Adjusted & copied from payment::create where referenceable_type = 'CashAdvance'
            $cashAdvance = CashAdvance::find($detail['cash_advance_id']);
            if ($cashAdvance->amount_remaining < $detail['amount']) {
                throw new PointException('Amount is over pay');
            }
            $payment->amount = $payment->amount - $detail['amount'];
            $cashAdvance->payments()->attach($payment->id);
            $cashAdvance->amount_remaining = $cashAdvance->amount_remaining - $detail['amount'];
            if ($cashAdvance->amount_remaining == 0) {
                $cashAdvance->form->done = 1;
                $cashAdvance->form->save();
            }
            $cashAdvance->save();

            $data['activity'] = ucfirst(strtolower($cashAdvance->payment_type)) . ' Out Withdrawal (' . $form->number . ')';
            CashAdvance::mapHistory($cashAdvance, $data);
        }, $data['cashAdvances']);
    }

    private static function mapAllocationReports($data, $payment)
    {
        return array_map(function ($detail) use ($data, $payment) {
            if ($detail['allocation_id']) {
                $allocationReport = new AllocationReport;
                $allocationReport->allocation_id = $detail['allocation_id'];
                $allocationReport->allocationable_id = $payment->id;
                $allocationReport->allocationable_type = $payment::$morphName;
                $allocationReport->form_id = $payment->form->id;
                $allocationReport->notes = $detail['notes'];

                $allocationReport->save();
            }
        }, $data['details']);
    }

    private static function calculateAmount($paymentDetails)
    {
        return array_reduce($paymentDetails, function ($carry, $detail) {
            return $carry + $detail['amount'];
        }, 0);
    }

    private static function mapPaymentCollectionJournals($payment, $data)
    {
        $journals = [];
        $journals['debit'] = array();
        $journals['credit'] = array();
        foreach ($data['details'] as $detail) {
            $paymentDetail = new PaymentDetail;
            $paymentDetail->fill($detail);
            $paymentDetail->referenceable_type = $data['referenceable_type'] ?? null;
            $paymentDetail->referenceable_id = $data['referenceable_id'] ?? null;

            $journal = new Journal;
            $journal->form_id_reference = optional(optional($paymentDetail->referenceable)->form)->id;
            $journal->notes = $paymentDetail->notes;
            $journal->chart_of_account_id = $paymentDetail->chart_of_account_id;

            if ($detail['payment_collection_type'] === 'SalesDownPayment') {
                $journal->debit = $paymentDetail->amount;
                $journals['debit'][] = $journal;
            } else if ($detail['payment_collection_type'] === 'SalesReturn') {
                $journal->debit = $paymentDetail->amount;
                $journals['debit'][] = $journal;
            } else if ($detail['payment_collection_type'] === 'Cost') {
                $journal->debit = $paymentDetail->amount;
                $journals['debit'][] = $journal;
            } else if ($detail['payment_collection_type'] === 'Income') {
                $journal->credit = $paymentDetail->amount;
                $journals['credit'][] = $journal;
            } else {
                $journal->credit = $paymentDetail->amount;
                $journals['credit'][] = $journal;
            }
        }
        error_log(json_encode($payment->amount));
        return $journals;
    }

    private static function generateFormNumber($payment, $number, $increment)
    {
        $defaultFormat = '{payment_type}/{disbursed}/{y}{m}{increment=4}';
        $formNumber = $number ?? $defaultFormat;

        preg_match_all('/{increment=(\d)}/', $formNumber, $regexResult);
        foreach ($regexResult[0] as $key => $value) {
            $padUntil = $regexResult[1][$key];
            $result = str_pad($increment, $padUntil, '0', STR_PAD_LEFT);
            $formNumber = str_replace($value, $result, $formNumber);
        }

        // Additional template for payment_type and disbursed
        if (strpos($formNumber, '{payment_type}') !== false) {
            $formNumber = str_replace('{payment_type}', $payment->payment_type, $formNumber);
        }
        if (strpos($formNumber, '{disbursed}') !== false) {
            $replacement = $payment->disbursed ? 'OUT' : 'IN';
            $formNumber = str_replace('{disbursed}', $replacement, $formNumber);
        }

        return $formNumber;
    }

    /**
     * Different method to get increment
     * because payment number is
     * considering payment_type and disbursed.
     *
     * @param $payment
     * @param $incrementGroup
     * @return int
     */
    private static function getLastPaymentIncrement($payment, $incrementGroup)
    {
        $lastPayment = self::from(self::getTableName() . ' as ' . self::$alias)
            ->joinForm()
            ->where('form.increment_group', $incrementGroup)
            ->whereNotNull('form.number')
            ->where(self::$alias . '.payment_type', $payment->payment_type)
            ->where(self::$alias . '.disbursed', $payment->disbursed)
            ->with('form')
            ->orderBy('form.increment', 'desc')
            ->select(self::$alias . '.*')
            ->first();

        $increment = 1;
        if (!empty($lastPayment)) {
            $increment += $lastPayment->form->increment;
        }

        return $increment;
    }

    private static function updateReferenceDone($paymentDetails)
    {
        foreach ($paymentDetails as $paymentDetail) {
            if ($paymentDetail->isDownPayment()) {
                $reference = $paymentDetail->referenceable;
                $reference->remaining -= $paymentDetail->amount;
                $reference->save();
                $reference->updateStatus();
            }
        }
    }

    private static function updateJournal($payment)
    {
        $journal = new Journal;
        $journal->form_id = $payment->form->id;
        $journal->journalable_type = $payment->paymentable_type;
        $journal->journalable_id = $payment->paymentable_id;
        $journal->chart_of_account_id = $payment->payment_account_id;
        if (!$payment->disbursed) {
            $journal->debit = $payment->amount;
        } else {
            $journal->credit = $payment->amount;
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
        }
    }

    private static function updateJournalPaymentCollection($payment, $journalsPaymentCollection)
    {
        $journal = new Journal;
        $journal->form_id = $payment->form->id;
        $journal->journalable_type = $payment->paymentable_type;
        $journal->journalable_id = $payment->paymentable_id;
        $journal->chart_of_account_id = $payment->payment_account_id;
        $journal->debit = $payment->amount;
        $journal->save();

        foreach ($journalsPaymentCollection['debit'] as $journal) {
            $journal->form_id = $payment->form->id;
            $journal->journalable_type = $payment->paymentable_type;
            $journal->journalable_id = $payment->paymentable_id;
            $journal->save();
        }

        foreach ($journalsPaymentCollection['credit'] as $journal) {
            $journal->form_id = $payment->form->id;
            $journal->journalable_type = $payment->paymentable_type;
            $journal->journalable_id = $payment->paymentable_id;
            $journal->save();
        }
    }
}
