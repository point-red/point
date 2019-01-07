<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use App\Model\Accounting\Journal;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;

class AccountPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $accounts = optional(\App\Helpers\Accounting\Account::currentLiabilities())->pluck('id') ?? [];

        $journalPayments = $this->getJournalPayments($accounts);

        $journals = Journal::leftJoinSub($journalPayments, 'journal_payment', function ($join) {
            $join->on('journals.form_number', '=', 'journal_payment.form_number_reference');
        })->selectRaw('SUM(credit) as credit')
            ->addSelect('journals.date', 'journals.form_number', 'journal_payment.debit')
            ->whereIn('chart_of_account_id', $accounts)
            ->where('credit', '>', 0)
            ->groupBy('form_number');

        // Filter Status | null = all / settled / unsettled
        $journals = $this->filterStatus($journals, $request->get('status'));

        // Filter Account Payable aging (days)
        $journals = $this->filterAging($journals, $request->get('age'));

        // Filter Debt owner
        $journals = $this->filterOwner($journals, $request->get('owner_id'));

        // Filter Specific invoice
        $journals = $this->filterForm($journals, $request->get('form_number'));

        return new ApiCollection($journals);
    }

    private function filterStatus($journals, $option)
    {
        if ($option === 'settled') {
            return $journals->havingRaw('credit - debit = 0');
        } elseif ($option === 'unsettled') {
            return $journals->havingRaw('credit - debit > 0');
        }
    }

    private function filterAging($journals, $age)
    {
        return $journals->where('date', now()->subDay($age));
    }

    private function filterOwner($journals, $ownerId)
    {
        return $journals->where('journalable_type', Supplier::class)->where('journalable_id', $ownerId);
    }

    private function filterForm($journals, $formNumber)
    {
        return $journals->where('form_number', $formNumber);
    }

    private function getJournalPayments($accounts)
    {
        return Journal::selectRaw('SUM(debit) as debit')
            ->addSelect('form_number_reference')
            ->whereIn('chart_of_account_id', $accounts)
            ->where('debit', '>', 0)
            ->groupBy('form_number_reference');
    }
}
