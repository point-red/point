<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use App\Model\Accounting\Journal;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;

class AccountReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $accounts = optional(\App\Helpers\Accounting\Account::accountReceivables())->pluck('id') ?? [];

        $journalPayments = $this->getJournalPayments($accounts);

        $journals = Journal::leftJoinSub($journalPayments, 'journal_payment', function ($join) {
            $join->on('journals.form_id', '=', 'journal_payment.form_id_reference');
        })->selectRaw('SUM(debit) as debit')
            ->addSelect('journals.date', 'journals.form_id', 'journal_payment.credit')
            ->whereIn('chart_of_account_id', $accounts)
            ->where('debit', '>', 0)
            ->groupBy('form_id');

        // Filter Status | null = all / settled / unsettled
        $journals = $this->filterStatus($journals, $request->get('status'));

        // Filter Account Receivable aging (days)
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
            return $journals->havingRaw('debit - credit = 0');
        } elseif ($option === 'unsettled') {
            return $journals->havingRaw('debit - credit > 0');
        }
    }

    private function filterAging($journals, $age)
    {
        return $journals->where('date', now()->subDay($age));
    }

    private function filterOwner($journals, $ownerId)
    {
        return $journals->where('journalable_type', Customer::class)->where('journalable_id', $ownerId);
    }

    private function filterForm($journals, $formNumber)
    {
        return $journals->where('form_number', $formNumber);
    }

    private function getJournalPayments($accounts)
    {
        return Journal::selectRaw('SUM(credit) as credit')
            ->addSelect('form_id_reference')
            ->whereIn('chart_of_account_id', $accounts)
            ->where('credit', '>', 0)
            ->groupBy('form_id_reference');
    }
}
