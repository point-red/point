<?php

namespace App\Model\Master;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;
use App\Model\MasterModel;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;

class Supplier extends MasterModel
{
    public static $morphName = 'Supplier';

    protected $connection = 'tenant';

    protected $fillable = [
        'code',
        'name',
        'tax_identification_number',
        'notes',
        'disabled',
    ];

    /**
     * Get all of the groups for the supplier.
     */
    public function groups()
    {
        return $this->belongsToMany(SupplierGroup::class);
    }

    /**
     * Get all of the supplier's contact persons.
     */
    public function contactPersons()
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    /**
     * Get all of the supplier's address.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all of the supplier's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all of the supplier's emails.
     */
    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    /**
     * Get all of the supplier's banks.
     */
    public function banks()
    {
        return $this->morphMany(Bank::class, 'bankable');
    }

    /**
     * Get all of the supplier's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    /**
     * Get the supplier's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Get the supplier's purchase receives.
     */
    public function purchaseReceives()
    {
        return $this->hasMany(PurchaseReceive::class);
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
