<?php

namespace App\Model\Master;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\MasterModel;
use App\Model\Accounting\Journal;
use App\Model\Finance\Payment\Payment;

class Customer extends MasterModel
{
    public static $morphName = 'Customer';

    protected $connection = 'tenant';

    protected $casts = ['credit_ceiling' => 'double'];

    protected $fillable = [
        'code',
        'name',
        'tax_identification_number',
        'notes',
        'credit_ceiling',
        'pricing_group_id',
        'disabled',
    ];

    /**
     * Get all of the groups for the customer.
     */
    public function groups()
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    /**
     * Get all of the customer's contact persons.
     */
    public function contactPersons()
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    /**
     * Get all of the customer's address.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all of the customer's phones.
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all of the customer's emails.
     */
    public function emails()
    {
        return $this->morphMany(Email::class, 'emailable');
    }

    /**
     * Get all of the customer's banks.
     */
    public function banks()
    {
        return $this->morphMany(Bank::class, 'bankable');
    }

    /**
     * Get all of the customer's journals.
     */
    public function journals()
    {
        return $this->morphMany(Journal::class, 'journalable');
    }

    /**
     * Get the customer's pricing group.
     */
    public function pricingGroup()
    {
        return $this->belongsTo(PricingGroup::class);
    }

    /**
     * Get the customer's payment.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function totalAccountPayable()
    {
        $totalCredit = $this->morphMany(Journal::class, 'journalable')
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'current liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'long term liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other current liability');
            })->sum('credit');
        $totalDebit = $this->morphMany(Journal::class, 'journalable')
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'current liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'long term liability')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other current liability');
            })->sum('debit');

        return $totalCredit - $totalDebit;
    }

    public function totalAccountReceivable()
    {
        $totalCredit = $this->morphMany(Journal::class, 'journalable')
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'account receivable')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other account receivable');
            })->sum('credit');
        $totalDebit = $this->morphMany(Journal::class, 'journalable')
            ->join(ChartOfAccount::getTableName(), ChartOfAccount::getTableName('id'), '=', Journal::getTableName('chart_of_account_id'))
            ->join(ChartOfAccountType::getTableName(), ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'))
            ->where(function ($query) {
                $query->where(ChartOfAccountType::getTableName('name'), '=', 'account receivable')
                    ->orWhere(ChartOfAccountType::getTableName('name'), '=', 'other account receivable');
            })->sum('debit');

        return $totalDebit - $totalCredit;
    }
}
