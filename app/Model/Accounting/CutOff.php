<?php

namespace App\Model\Accounting;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\FixedAsset;
use App\Model\TransactionModel;
use App\Traits\Model\General\FormableOne;
use App\Traits\Model\General\GeneralJoin;
use Illuminate\Support\Facades\DB;

class CutOff extends TransactionModel
{
    use GeneralJoin, FormableOne;

    protected $fillable = [
        'date',
        'chart_of_account_id',
        'credit',
        'debit',
    ];

    protected $casts = [
        'debit' => 'double',
        'credit' => 'double',
    ];

    public static $morphName = 'CutOff';

    protected $connection = 'tenant';

    public static $alias = 'cut_off';

    protected $table = 'cutoffs_new';

    public $defaultNumberPrefix = 'CUT';

    /**
     * Get all of the item's journals.
     */
    public function cutOffAccount()
    {
        return $this->hasMany(CutOffAccount::class, 'cutoff_id');
    }

    public static function createCutoff($reqData)
    {
        $chartOfAccounts = ChartOfAccount::with('type')->findOrFail(array_column($reqData['details'], "chart_of_account_id"));     
            
        DB::connection('tenant')->beginTransaction();

        $cutOff = self::saveCutoff($reqData);
        $form = self::saveForm($reqData, $cutOff);
        $labaDitahan = self::getAccountForLabaDitahan();

        foreach ($reqData['details'] as $cutOffReq) {
            $chartOfAccount = array_first($chartOfAccounts, function ($item) use ($cutOffReq) {
                return $item->id == $cutOffReq['chart_of_account_id'];
            });

            $cutOffAccount = self::saveCutoffAccount($chartOfAccount, $cutOff, $cutOffReq);
            self::saveJournal($form, $cutOffAccount, $chartOfAccount, $labaDitahan);

            self::saveCutOffAble($form, $labaDitahan, $cutOffAccount, $chartOfAccount, $cutOffReq);
        }

        DB::connection('tenant')->commit();
        return $cutOff;
    }

    private static function saveCutoff($reqData)
    {
        $cutOff = new CutOff;
        $cutOff->fill($reqData);
        $cutOff->save();
        return $cutOff;
    }

    private static function saveForm($reqData, $cutOff)
    {
        $form = new Form();
        $form->saveData($reqData, $cutOff);
        return $form;
    }

    private static function saveCutoffAccount($chartOfAccount, $cutOff, $data)
    {
        $cutOffAccount = new CutOffAccount();
        $cutOffAccount->cutoff_id = $cutOff->id;
        $cutOffAccount->chart_of_account_id = $chartOfAccount->id;
        $cutOffAccount->debit = $data['debit'];
        $cutOffAccount->credit = $data['credit'];
        $cutOffAccount->save();
        return $cutOffAccount;
    }

    private static function saveJournal($form, $cutOffAccount, $chartOfAccount, $labaDitahan)
    {
        $journal = new Journal;
        $journal->form_id = $form->id;
        $journal->chart_of_account_id = $chartOfAccount->id;
        $journal->debit = $cutOffAccount->debit;
        $journal->credit = $cutOffAccount->credit;
        $journal->save();

        $journal1 = new Journal;
        $journal1->form_id = $form->id;
        $journal1->chart_of_account_id = $labaDitahan->id;
        $journal1->debit = $cutOffAccount->credit;
        $journal1->credit = $cutOffAccount->debit;
        $journal1->save();
    }

    private static function getAccountForLabaDitahan()
    {
        $accountName = 'LABA DITAHAN';
        $labaDitahan = ChartOfAccount::where('alias', $accountName)->first();
        if (!$labaDitahan){
            $typeId = ChartOfAccountType::where('name', 'RETAINED EARNING')->first()->id;
            
            $labaDitahan = new ChartOfAccount;
            $labaDitahan->type_id = $typeId;
            $labaDitahan->number = "32000";
            $labaDitahan->name = "RETAINED EARNING";
            $labaDitahan->alias = $accountName;
            $labaDitahan->position = "CREDIT";
            $labaDitahan->is_locked = true;
            $labaDitahan->save();
        }
        return $labaDitahan;
    }

    private static function saveCutOffAble($form, $labaDitahan, $cutOffAccount, $chartOfAccount, $data)
    {
        foreach ($data['items'] as $item) {
            $cutoffAble = null;
            $cutoffAbleType = self::getCutOffAbleType($chartOfAccount);

            if ($cutableItem = self::saveCutOffAbleItem($form, $chartOfAccount, $item)) $cutoffAble = $cutableItem;
            elseif ($cutableAsset = self::saveCutOffAbleAsset($form, $labaDitahan, $chartOfAccount, $item)) $cutoffAble = $cutableAsset;
            elseif ($cutableDownPayment = self::saveCutOffAbleDownPayment($chartOfAccount, $item)) $cutoffAble = $cutableDownPayment;
            elseif ($cutablePayment = self::saveCutOffAblePayment($chartOfAccount, $item)) $cutoffAble = $cutablePayment;

            if ($cutoffAble) {
                $cutoffAble->fill($item);
                $cutoffAble->chart_of_account_id = $chartOfAccount->id;
                $cutoffAble->save();

                $cutOffDetail = new CutOffDetail();
                $cutOffDetail->cutoff_account_id = $cutOffAccount->id;
                $cutOffDetail->chart_of_account_id = $chartOfAccount->id;
                $cutOffDetail->cutoffable_id = $cutoffAble->id;
                $cutOffDetail->cutoffable_type = $cutoffAbleType;
                $cutOffDetail->save();
            }
        }
    }

    private static function getCutOffAbleType($chartOfAccount)
    {
        $cutoffAbleType = null;
        $subLedger = trim($chartOfAccount->sub_ledger);
        if ($subLedger === 'ITEM') $cutoffAbleType = CutOffInventory::$morphName;
        elseif ($subLedger === 'FIXED ASSET') $cutoffAbleType = CutOffAsset::$morphName;
        elseif (strpos($chartOfAccount->type->name, 'DOWN PAYMENT') !== FALSE &&
            in_array($subLedger, ['CUSTOMER', 'SUPPLIER', 'EXPEDITION', 'EMPLOYEE'])) $cutoffAbleType = CutOffDownPayment::$morphName;
        elseif (in_array($subLedger, ['CUSTOMER', 'SUPPLIER', 'EXPEDITION', 'EMPLOYEE'])) $cutoffAbleType = CutOffPayment::$morphName;

        return $cutoffAbleType;
    }

    private static function saveCutOffAbleItem($form, $chartOfAccount, $data)
    {
        if (trim($chartOfAccount->sub_ledger) !== 'ITEM') return false;
        
        $cutoffAble = new CutOffInventory();
        $cutoffAble->item_id = $data['object_id'];

        if (isset($data['dna']) && is_array($data['dna'])) {
            foreach($data['dna'] as $dnaItem) {
                $itemDna = new CutOffInventoryDna();
                $itemDna->fill($dnaItem);
                $itemDna->item_id = $data['object_id'];
                $itemDna->save();
            }
        }
        self::saveInventory($form, $data);
        return $cutoffAble;
    }

    private static function saveInventory($form, $data) {
        if (isset($data['dna']) && is_array($data['dna'])) {
            foreach($data['dna'] as $dnaItem) {
                $inventory = new Inventory();
                $inventory->form_id = $form->id;
                $inventory->warehouse_id = $data['warehouse_id'];
                $inventory->item_id = $data['object_id'];
                $inventory->unit_reference = $data['unit'];
                $inventory->converter_reference = $data['converter'];
                $inventory->quantity = $dnaItem['quantity'];
                $inventory->quantity_reference = $dnaItem['quantity'];
                $inventory->expiry_date = $dnaItem['expiry_date'];
                $inventory->production_number = $dnaItem['production_number'];
                $inventory->save();
            }
        }else {
            $inventory = new Inventory();
            $inventory->form_id = $form->id;
            $inventory->warehouse_id = $data['warehouse_id'];
            $inventory->item_id = $data['object_id'];
            $inventory->unit_reference = $data['unit'];
            $inventory->converter_reference = $data['converter'];
            $inventory->quantity = $data['quantity'];
            $inventory->quantity_reference = $data['quantity'];
            $inventory->save();
        }
    }

    private static function saveCutOffAbleAsset($form, $labaDitahan, $chartOfAccount, $data)
    {
        if (trim($chartOfAccount->sub_ledger) !== 'FIXED ASSET') return false;

        $cutoffAble = new CutOffAsset();
        $cutoffAble->fixed_asset_id = $data['object_id'];

        self::saveAssetDepreciation($form, $labaDitahan, $chartOfAccount, $data);
        return $cutoffAble;
    }

    private static function saveAssetDepreciation($form, $labaDitahan, $chartOfAccount, $data)
    {
        $fixedAsset = FixedAsset::findOrFail($data['object_id']);
        if ($fixedAsset->depreciation_method === FixedAsset::$DEPRECIATION_METHOD_STRAIGHT_LINE) {
            $journal = new Journal;
            $journal->form_id = $form->id;
            $journal->chart_of_account_id = $fixedAsset->accumulation_chart_of_account_id;
            $journal->debit = $chartOfAccount->position === 'DEBIT' ? $data['accumulation'] : 0;
            $journal->credit = $chartOfAccount->position === 'CREDIT' ? $data['accumulation'] : 0;
            $journal->save();

            $journal1 = new Journal;
            $journal1->form_id = $form->id;
            $journal1->chart_of_account_id = $labaDitahan->id;
            $journal1->debit = $journal->credit;
            $journal1->credit = $journal->debit;
            $journal1->save();
        }
    }

    private static function saveCutOffAbleDownPayment($chartOfAccount, $data)
    {
        if (strpos($chartOfAccount->type->name, 'DOWN PAYMENT') === FALSE ||
            !in_array(trim($chartOfAccount->sub_ledger), ['CUSTOMER', 'SUPPLIER', 'EXPEDITION', 'EMPLOYEE'])) return false;
        
        $cutoffAble = new CutOffDownPayment();
        $cutoffAble->cutoff_downpaymentable_id = $data['object_id'];
        $cutoffAble->cutoff_downpaymentable_type = CutOffDownPayment::getCutOffDownPaymentableType(trim($chartOfAccount->sub_ledger));
        $cutoffAble->payment_type = $chartOfAccount->position === 'DEBIT' ? 'RECEIVABLE' : 'PAYABLE';
        return $cutoffAble;
    }

    private static function saveCutOffAblePayment($chartOfAccount, $data)
    {
        $cutoffAble = new CutOffPayment();
        $cutoffAble->cutoff_paymentable_type = CutOffPayment::getCutOffPaymentableType(trim($chartOfAccount->sub_ledger));
        $cutoffAble->payment_type = $chartOfAccount->position === 'DEBIT' ? 'RECEIVABLE' : 'PAYABLE';
        $cutoffAble->cutoff_paymentable_id = $data['object_id'];
        return $cutoffAble;
    }
}
