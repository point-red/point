<?php

namespace App\Http\Controllers\Api\Finance\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use App\Model\Accounting\Journal;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;
use App\Model\HumanResource\Employee\Employee;
use App\Model\UserActivity;
use App\Model\Form;
use App\Model\FormChecklist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
       
    public function index(Request $request)
    {
        $reports = $this->generateReports($request);

        $reports['data'] = $this->paginate($reports['data'], $request->get('limit'), $request->get('page'));
        return json_encode($reports);
    }

    public function setChecklist(Request $request)
    {
        $checklist = FormChecklist::where('number', $request->get('number'))->where('feature', $request->get('report_name'))->first();
        if(!$checklist){
            $checklist = FormChecklist::create($request);
        }
        $checklist->is_checked = $request->get('is_checked');
        $checklist->save();

        return json_encode($checklist);
    }

    private function paginate($items, $perPage = 1000, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function generateReports($request)
    {
        $payments = Payment::from(Payment::getTableName().' as '.Payment::$alias)
                    ->fields('payment.*')
                    ->sortBy('form.date')
                    ->includes('form;details.chartOfAccount;details.allocation;paymentable')
                    ->filterEqual(['payment.payment_type' => strtoupper($request->get('report_type'))])
                    ->filterLike([
                        'form.number' => $request->get('search'),
                        'payment_detail.notes' => $request->get('search'),
                        'account.alias' => $request->get('search'),
                        'account.number' => $request->get('search'),
                        Customer::$alias.'.name' => $request->get('search'),
                        Supplier::$alias.'.name' => $request->get('search'),
                        Employee::$alias.'.name' => $request->get('search'),
                    ])
                    ->filterDateMin($request->get('filter_date_min'))
                    ->filterDateMax($request->get('filter_date_max'))
                    ->filterForm($request->get('filter_form'));
        
        if($request->get('account_id') != null){
            $payments->filterEqual(['payment_account.id' => $request->get('account_id')]);
        }

        if($request->get('journal_account_id') != null){
            $payments->filterEqual(['account.id' => $request->get('journal_account_id')]);
        }

        if($request->get('subledger_id') != null && $request->get('subledger_type') != null){
            $payments->filterEqual(['payment.paymentable_type' => $request->get('subledger_type')])
                    ->filterEqual(['payment.paymentable_id' => $request->get('subledger_id')]);
        }

        $payments = Payment::joins($payments, 'form,payment_account,details,account,allocation,paymentable')->get();

        $reports['data'] = [];
        foreach($payments as $payment){
            foreach($payment->details as $detail){
                $data = [];
                $data['id'] = $payment->id;
                $data['date'] = $payment->form->date; 
                $data['form_number'] = $payment->form->number;
                $data['subledger'] = $payment->paymentable_name;
                $data['notes'] = $detail->notes;
                $data['account_id'] = $detail->chartOfAccount->id;
                $data['account_number'] = $detail->chartOfAccount->number;
                $data['account_alias'] = $detail->chartOfAccount->alias;
                $data['is_checked'] = $payment->form->getChecklist($request->get('report_name'));
                if(!$payment->disbursed){
                    $data['debit'] = $detail->amount;
                    $data['credit'] = 0.0;
                    $data['type'] = 'in';
                }elseif($payment->disbursed){
                    $data['debit'] = 0.0;
                    $data['credit'] = $detail->amount;
                    $data['type'] = 'out';
                }
                array_push($reports['data'], $data);
            }
        }

        $cuttoffs = CutOffAccount::from(CutOffAccount::getTableName().' as '.CutOffAccount::$alias)
                    ->fields('cutoff_accounts.id;cutoff_id;chart_of_account_id;cutoff_accounts.debit;cutoff_accounts.credit;cutoff_accounts.created_at;cutoff_accounts.updated_at')
                    ->sortBy('form.date')
                    ->includes('chartOfAccount')
                    ->filterEqual(['account_type.name' => strtoupper($request->get('report_type'))])
                    ->filterLike([
                        'form.number' => $request->get('search'),
                        'form.notes' => $request->get('search'),
                    ])
                    ->filterDateMin($request->get('filter_date_min'))
                    ->filterDateMax($request->get('filter_date_max'));
        
        if($request->get('account_id') != null){
            $cuttoffs->filterEqual(['account.id' => $request->get('account_id')]);
        }

        $cuttoffs = CutOffAccount::joins($cuttoffs, 'account,account_type,cutoff.form')->get();

        foreach($cuttoffs as $cutoff){
            $data = [];
            $data['id'] = $cutoff->chartOfAccount->id;
            $data['date'] = $cutoff->cutoff->form->date; 
            $data['form_number'] = $cutoff->cutoff->form->number;
            $data['subledger'] = null;
            $data['notes'] = $cutoff->cutoff->form->notes;
            $data['account_id'] = $cutoff->chartOfAccount->id;
            $data['account_number'] = $cutoff->chartOfAccount->number;
            $data['account_alias'] = $cutoff->chartOfAccount->alias;
            $data['is_checked'] = $cutoff->cutoff->form->getChecklist($request->get('report_name'));
            $data['debit'] = $cutoff->debit;
            $data['credit'] = $cutoff->credit;
            $data['type'] = 'cut-off';
            array_push($reports['data'], $data);
        }
        
        usort($reports['data'], function ($x, $y) {
            return $x['date'] <=> $y['date'];
        });

        $index = 0;
        $balance = 0;
        $total['debit'] = 0;
        $total['credit'] = 0;
        foreach($reports['data'] as $report){
            $balance += $report['debit'] - $report ['credit'];
            $total['debit'] += $report['debit'];
            $total['credit'] += $report['credit'];
            $reports['data'][$index]['balance'] = $balance;
            $index++;
        }

        $reports['opening_balance'] = $this->openingBalance($request);
        $reports['total'] = $total;
        $reports['ending_balance'] = $total['debit'] - $total['credit'] + $reports['opening_balance']['debit'] - $reports['opening_balance']['credit'];

        $cashAdvance = $this->reportsCashAdvance($request);

        $reports['cash_advance'] = (int)$cashAdvance->amount_remaining_total;

        return $reports;
    }

    private function openingBalance($request)
    {
        $payments = Payment::from(Payment::getTableName().' as '.Payment::$alias)
                    ->fields('payment.*')
                    ->sortBy('form.date')
                    ->filterEqual(['payment.payment_type' => strtoupper($request->get('report_type'))])
                    ->filterDateMax($request->get('filter_date_min'))
                    ->filterForm($request->get('filter_form'));
        
        if($request->get('account_id') != null){
            $payments->filterEqual(['payment_account.id' => $request->get('account_id')]);
        }

        if($request->get('journal_account_id') != null){
            $payments->filterEqual(['account.id' => $request->get('journal_account_id')]);
        }

        if($request->get('subledger_id') != null && $request->get('subledger_type') != null){
            $payments->filterEqual(['payment.paymentable_type' => $request->get('subledger_type')])
                    ->filterEqual(['payment.paymentable_id' => $request->get('subledger_id')]);
        }

        $payments = Payment::joins($payments, 'form,payment_account,details,account,allocation,paymentable')->get();

        $balance['debit'] = 0;
        $balance['credit'] = 0;
        foreach($payments as $payment){
            foreach($payment->details as $detail){
                if($payment->disbursed == false){
                    $balance['debit'] +=  $detail->amount;
                }else {
                    $balance['credit'] += $detail->amount;
                }
            }
        }

        $cuttoffs = CutOffAccount::from(CutOffAccount::getTableName().' as '.CutOffAccount::$alias)
                    ->fields('cutoff_accounts.debit;cutoff_accounts.credit')
                    ->sortBy('form.date')
                    ->filterEqual(['account_type.name' => strtoupper($request->get('report_type'))])
                    ->filterLike([
                        'form.number' => $request->get('search'),
                        'form.notes' => $request->get('search'),
                    ])
                    ->filterDateMax($request->get('filter_date_min'));
        
        if($request->get('account_id') != null){
            $cuttoffs->filterEqual(['account.id' => $request->get('account_id')]);
        }

        $cuttoffs = CutOffAccount::joins($cuttoffs, 'account,account_type,cutoff.form')->get();

        foreach($cuttoffs as $cutoff){
            $balance['debit'] += $cutoff->debit; 
            $balance['credit'] += $cutoff->credit;
        }
        return $balance;
    }

    private function reportsCashAdvance($request)
    {
        $cashAdvance = CashAdvance::from(CashAdvance::getTableName().' as '.CashAdvance::$alias)
                    ->fields('raw:sum(cash_advance.amount_remaining) as amount_remaining_total')
                    ->sortBy('form.date')
                    ->filterEqual(['cash_advance.payment_type' => strtoupper($request->get('report_type'))])
                    ->filterDateMin($request->get('filter_date_min'))
                    ->filterDateMax($request->get('filter_date_max'))
                    ->filterForm('approvalApproved;notCanceled')
                    ->filterForm($request->get('filter_form'));
        
        if($request->get('account_id') != null){
            $cashAdvance->filterEqual(['cash_advance_detail.chart_of_account_id' => $request->get('account_id')]);
        }

        if($request->get('subledger_id') != null && $request->get('subledger_type') != null && strtolower($request->get('subledger_type'))=='employee'){
            $cashAdvance->filterEqual(['cash_advance.employee_id' => $request->get('subledger_id')]);
        }

        $cashAdvance = CashAdvance::joins($cashAdvance, 'form,employee,details,account')->first();

        return $cashAdvance;
    }

}
