<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Accounting\Journal;
use App\Model\Form;
use App\Model\Master\Supplier;
use Illuminate\Http\Request;

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
        $accounts = optional(\App\Helpers\Accounting\Account::accountPayables())->pluck('id') ?? [];

        $journalPayments = $this->getJournalPayments($accounts);

        $journals = Journal::join(Form::getTableName(), Form::getTableName('id'), '=', Journal::getTableName('form_id'))
            ->leftJoinSub($journalPayments, 'journal_payment', function ($join) {
                $join->on('journals.form_id', '=', 'journal_payment.form_id_reference');
            })
            ->selectRaw('SUM(credit) as credit')
            ->addSelect('forms.date', 'journals.form_id', \DB::raw('IFNULL(journal_payment.debit, 0) AS debit'))
            ->whereIn('chart_of_account_id', $accounts)
            ->where('credit', '>', 0)
            ->groupBy('forms.id');

        // Filter Status | null = all / settled / unsettled
        $journals = $this->filterStatus($journals, $request->get('status'));

        // Filter Account Payable aging (days)
        if ($request->has('age')) {
            $journals = $this->filterAging($journals, $request->get('age'));
        }

        // Filter Debt owner
        if ($request->has('owner_id')) {
            $journals = $this->filterOwner($journals, $request->get('owner_id'));
        }

        // Filter Specific invoice
        if ($request->has('form_number')) {
            $journals = $this->filterForm($journals, $request->get('form_number'));
        }

        $journals = pagination($journals, $request->get('limit'));

        return new ApiCollection($journals);
    }

    private function filterStatus($journals, $option)
    {
        if ($option === 'settled') {
            return $journals->havingRaw('credit - debit = 0');
        } elseif ($option === 'unsettled') {
            return $journals->havingRaw('credit - debit > 0');
        }

        return $journals;
    }

    private function filterAging($journals, $age)
    {
        return $journals->where('forms.date', now()->subDay($age));
    }

    private function filterOwner($journals, $ownerId)
    {
        return $journals->where('journalable_type', Supplier::$morphName)->where('journalable_id', $ownerId);
    }

    private function filterForm($journals, $formNumber)
    {
        return $journals->where('forms.number', $formNumber);
    }

    private function getJournalPayments($accounts)
    {
        return Journal::selectRaw('SUM(debit) as debit')
            ->addSelect('form_id_reference')
            ->whereIn('chart_of_account_id', $accounts)
            ->where('debit', '>', 0)
            ->groupBy('form_id_reference');
    }
}
