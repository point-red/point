<?php

namespace App\Model\Master;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\Journal;
use App\Model\MasterModel;
use App\Traits\Model\Master\SupplierJoin;
use App\Traits\Model\Master\SupplierRelation;

class Supplier extends MasterModel
{
    use SupplierRelation, SupplierJoin;

    protected $connection = 'tenant';

    protected $appends = ['label'];

    protected $fillable = [
        'code',
        'name',
        'tax_identification_number',
        'notes',
        'disabled',
    ];

    public static $alias = 'supplier';

    public static $morphName = 'Supplier';

    public function getLabelAttribute()
    {
        $label = $this->code ? '[' . $this->code . '] ' : '';

        return $label . $this->name;
    }

    /**
     * Get the supplier's total payable.
     */
    public function totalAccountPayable()
    {
        $payables = $this->journals()
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'current liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'long term liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other current liability');
            })
            ->selectRaw('SUM(`credit`) AS credit, SUM(`debit`) AS debit')
            ->first();

        return $payables->credit - $payables->debit;
    }

    /**
     * Get the supplier's total receivable.
     */
    public function totalAccountReceivable()
    {
        $receivables = $this->journals()
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'account receivable')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other account receivable');
            })
            ->selectRaw('SUM(`credit`) AS credit, SUM(`debit`) AS debit')
            ->first();

        return $receivables->debit - $receivables->credit;
    }
}
