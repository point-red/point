<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreRequest;
use App\Http\Resources\Accounting\CutOff\CutOffResource;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use App\Model\Accounting\CutOffAsset;
use App\Model\Accounting\CutOffDetail;
use App\Model\Accounting\CutOffDownPayment;
use App\Model\Accounting\CutOffInventory;
use App\Model\Accounting\CutOffInventoryDna;
use App\Model\Accounting\CutOffPayment;
use App\Model\Accounting\Journal;
use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffs = CutOff::eloquentFilter($request);
        $cutOffs = CutOff::joins($cutOffs, $request);

        $cutOffs = pagination($cutOffs, $request->get('limit'));

        return new ApiCollection($cutOffs);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return ApiCollection
     */
    public function indexByAccount(Request $request)
    {
        $cutOffs = CutOffAccount::eloquentFilter($request);
        $cutOffs = CutOffAccount::joins($cutOffs, $request);

        $cutOffs = pagination($cutOffs, $request->get('limit'));

        return new ApiCollection($cutOffs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(StoreRequest $request)
    {
        try {
            
            $chartOfAccounts = ChartOfAccount::with('type')->findOrFail(array_column($request->get("details"), "chart_of_account_id"));
            
            DB::connection('tenant')->beginTransaction();

            $cutOff = new CutOff;
            $cutOff->fill($request->all());
            $cutOff->save();

            $form = new Form;
            $form->saveData($request->all(), $cutOff);

            $labaDitahan = $this->getAccountForLabaDitahan();

            $details = $request->get('details', []);
            foreach ($details as $cutOffReq) {
                $chartOfAccount = array_first($chartOfAccounts, function ($item) use ($cutOffReq) {
                    return $item->id == $cutOffReq['chart_of_account_id'];
                });

                $cutOffAccount = new CutOffAccount();
                $cutOffAccount->cutoff_id = $cutOff->id;
                $cutOffAccount->chart_of_account_id = $chartOfAccount->id;
                $cutOffAccount->debit = $cutOffReq['debit'];
                $cutOffAccount->credit = $cutOffReq['credit'];
                $cutOffAccount->save();

                $journal = new Journal;
                $journal->form_id = $form->id;
                $journal->chart_of_account_id = $chartOfAccount->id;
                $journal->debit = $cutOffAccount->debit;
                $journal->credit = $cutOffAccount->credit;
                $journal->save();

                $journal1 = new Journal;
                $journal1->form_id = $form->id;
                $journal1->chart_of_account_id = $labaDitahan->id;
                $journal1->debit = $chartOfAccount->position === 'DEBIT' ? 0 : $cutOffAccount->debit;
                $journal1->credit = $chartOfAccount->position === 'CREDIT' ? 0 : $cutOffAccount->credit;
                $journal1->save();

                if ($chartOfAccount->sub_ledger) {
                    if (!isset($cutOffReq['items']) || count($cutOffReq['items']) < 1) throw new \App\Exceptions\PointException('Items can not be null');
                    $subLedger = trim($chartOfAccount->sub_ledger);
                    $items = isset($cutOffReq['items']) ? $cutOffReq['items'] : [];
                    foreach ($items as $item) {
                        $cutoffAble = null;
                        $cutoffAbleType = null;
                        if ($subLedger == 'ITEM') {
                            $cutoffAbleType = CutOffInventory::$morphName;
                            $cutoffAble = new CutOffInventory();
                            $cutoffAble->item_id = $item['object_id'];

                            if (isset($item['dna']) && is_array($item['dna']) && count($item['dna']) > 0) {
                                foreach($item['dna'] as $dnaItem) {
                                    $itemDna = new CutOffInventoryDna();
                                    $itemDna->fill($dnaItem);
                                    $itemDna->item_id = $item['object_id'];
                                    $itemDna->save();
                                }
                            }
                        } elseif ($subLedger == 'FIXED ASSET') {
                            $cutoffAbleType = CutOffAsset::$morphName;
                            $cutoffAble = new CutOffAsset();
                            $cutoffAble->fixed_asset_id = $item['object_id'];
                        } elseif (strpos($chartOfAccount->type->name, 'DOWN PAYMENT') !== FALSE && in_array($subLedger, ['CUSTOMER', 'SUPPLIER', 'EXPEDITION', 'EMPLOYEE'])) {
                            $cutoffAbleType = CutOffDownPayment::$morphName;
                            $cutoffAble = new CutOffDownPayment();
                            $cutoffAble->cutoff_downpaymentable_id = $item['object_id'];
                            $cutoffAble->cutoff_downpaymentable_type = CutOffDownPayment::getCutOffDownPaymentableType($subLedger);
                            $cutoffAble->payment_type = $chartOfAccount->position === 'DEBIT' ? 'RECEIVABLE' : 'PAYABLE';
                        } elseif (in_array($subLedger, ['CUSTOMER', 'SUPPLIER', 'EXPEDITION', 'EMPLOYEE'])) {
                            $cutoffAbleType = CutOffPayment::$morphName;
                            $cutoffAble = new CutOffPayment();
                            $cutoffAble->cutoff_paymentable_type = CutOffPayment::getCutOffPaymentableType($subLedger);
                            $cutoffAble->payment_type = $chartOfAccount->position === 'DEBIT' ? 'RECEIVABLE' : 'PAYABLE';
                            $cutoffAble->cutoff_paymentable_id = $item['object_id'];
                        }

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
            }

            DB::connection('tenant')->commit();
            return new ApiResource($cutOff);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            throw $e;
        }
    }

    private function getAccountForLabaDitahan() {
        $accountName = 'LABA DITAHAN';
        $labaDitahan = ChartOfAccount::where('alias', $accountName)->first();
        if (!$labaDitahan){
            $typeId = ChartOfAccountType::where('name', 'RETAINED EARNING')->first()->id;
            
            $chartOfAccount = new ChartOfAccount;
            $chartOfAccount->type_id = $typeId;
            $chartOfAccount->number = "32000";
            $chartOfAccount->name = "RETAINED EARNING";
            $chartOfAccount->alias = $accountName;
            $chartOfAccount->position = "CREDIT";
            $chartOfAccount->is_locket = true;
            $chartOfAccount->save();
        }
        return $labaDitahan;
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $cutOff = CutOffAccount::eloquentFilter($request)->findOrFail($id);
        return new ApiResource($cutOff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $cutOff = CutOff::findOrFail($id);
        $cutOff->form->date = $request->get('date');
        $cutOff->form->save();

        return new ApiResource($cutOff);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOff = CutOff::findOrFail($id);

        $cutOff->delete();

        Journal::where('journalable_type', CutOff::class)->where('journalable_id', $id)->delete();

        DB::connection('tenant')->commit();

        return new CutOffResource($cutOff);
    }
}
