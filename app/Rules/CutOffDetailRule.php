<?php

namespace App\Rules;

use App\Exceptions\PointException;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOffAsset;
use App\Model\Accounting\CutOffDownPayment;
use App\Model\Accounting\CutOffInventory;
use App\Model\Accounting\CutOffPayment;
use App\Model\Accounting\Journal;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CutOffDetailRule implements Rule
{
    private $message = "Invalid data";

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validate($value);
    }

    private function validate($value)
    {
        try{
            $this->nonSubledger($value);
            $this->validatePayment($value);
            $this->validateItem($value);
            $this->validateAsset($value);
            return true;
        } catch (PointException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    private function nonSubledger($cutoff)
    {
        $subLedger = trim($cutoff['chart_of_account_sub_ledger']);
        if (!$subLedger && Journal::select('id')->where('chart_of_account_id', $cutoff['chart_of_account_id'])->first()) {
            throw new PointException("Duplicate data entry");
        }
    }

    private function validatePayment($cutoff)
    {
        $subLedger = trim($cutoff['chart_of_account_sub_ledger']);
        if (!$subLedger || !in_array($subLedger, ['CUSTOMER', 'EXPEDITION', 'SUPPLIER', 'EMPLOYEE'])) {
            return true;
        }
        $model = CutOffPayment::class;
        $field = 'cutoff_paymentable_id';
        if (strpos($cutoff['chart_of_account_type']['name'], 'DOWN PAYMENT') !== FALSE) {
            $model = CutOffDownPayment::class;
            $field = 'cutoff_downpaymentable_id';
        }
        if ($model::where('chart_of_account_id', $cutoff['chart_of_account_id'])
                ->whereIn($field, array_column($cutoff['items'], 'object_id'))
                ->first()) {
                    throw new PointException("Duplicate data entry");
                }
    }

    private function validateItem($cutoff)
    {
        $subLedger = trim($cutoff['chart_of_account_sub_ledger']);
        if (!$subLedger || !in_array($subLedger, ['ITEM'])) {
            return true;
        }

        if (CutOffInventory::where('chart_of_account_id', $cutoff['chart_of_account_id'])
                ->whereIn('item_id', array_column($cutoff['items'], 'object_id'))
                ->first()) {
                    throw new PointException("Duplicate data entry");
                }
    }

    private function validateAsset($cutoff)
    {
        $subLedger = trim($cutoff['chart_of_account_sub_ledger']);
        if (!$subLedger || !in_array($subLedger, ['FIXED ASSET'])) {
            return true;
        }

        if (CutOffAsset::where('chart_of_account_id', $cutoff['chart_of_account_id'])
                ->whereIn('fixed_asset_id', array_column($cutoff['items'], 'object_id'))
                ->first()) {
                    throw new PointException("Duplicate data entry");
                }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
