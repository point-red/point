<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreAccountRequest;
use App\Http\Requests\Accounting\CutOff\UpdateAccountRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cutOffAccounts = CutOffAccount::eloquentFilter($request);

        $cutOffAccounts = $cutOffAccounts->join(ChartOfAccount::getTableName(), function ($q) {
            $q->on(ChartOfAccount::getTableName('id'), '=', CutOffAccount::getTableName('chart_of_account_id'));
        })->join(ChartOfAccountType::getTableName(), function ($q) {
            $q->on(ChartOfAccountType::getTableName('id'), '=', ChartOfAccount::getTableName('type_id'));
        })->whereIn(ChartOfAccountType::getTableName('name'), [
            'cash',
            'cash-bank transfer',
            'bank',
            'supplies',
            'note receivable',
            'account receivable',
            'account receivable of management',
            'account receivable of employee',
            'other account receivable',
            'purchase down payment',
            'income tax receivable',
            'inventory',
            'other current asset',
            'fixed asset',
            'fixed asset depreciation',
            'other asset',
            'other asset amortization',
            'current liability',
            'other current liability',
            'long term liability',
            'note payable',
            'account payable',
            'sales down payment',
            'income tax payable',
            'owner equity',
            'shareholder distribution',
            'retained earning'
        ]);

        $cutOffAccounts = pagination($cutOffAccounts, $request->get('limit'));

        return new ApiCollection($cutOffAccounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequest $request
     * @return ApiResource
     */
    public function store(StoreAccountRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->is_sub_ledger = $request->get('is_sub_ledger');
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('alias');
        $chartOfAccount->save();

        // create cut off account
        if (!CutOffAccount::where('chart_of_account_id', $chartOfAccount->id)->where('cut_off_id', CutOff::where('id', '>', 0)->first()->id)->first()) {
            $cutOffAccount = new CutOffAccount;
            $cutOffAccount->chart_of_account_id = $chartOfAccount->id;
            $cutOffAccount->cut_off_id = CutOff::where('id', '>', 0)->orderBy('id', 'desc')->first()->id;
            if ($chartOfAccount->type->is_debit == true) {
                $cutOffAccount->debit = $request->get('balance');
            } else {
                $cutOffAccount->credit = $request->get('balance');
            }

            $cutOffAccount->save();
        }

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccount);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAccountRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateAccountRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        // create cut off account
        $cutOffAccount = CutOffAccount::findOrFail($id);
        if ($cutOffAccount->chartOfAccount->type->is_debit == true) {
            $cutOffAccount->debit = $request->get('balance');
        } else {
            $cutOffAccount->credit = $request->get('balance');
        }

        $cutOffAccount->save();

        $cutOffAccount->chartOfAccount->type_id = $request->get('type_id');
        $cutOffAccount->chartOfAccount->number = $request->get('number') ?? null;
        $cutOffAccount->chartOfAccount->is_sub_ledger = $request->get('is_sub_ledger');
        $cutOffAccount->chartOfAccount->alias = $request->get('alias');
        $cutOffAccount->chartOfAccount->save();

        DB::connection('tenant')->commit();

        return new ApiResource($cutOffAccount);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOffAccount = CutOffAccount::findOrFail($id);

        $chartOfAccount = ChartOfAccount::findOrFail($cutOffAccount->chart_of_account_id);

        $cutOffAccount->delete();

        $chartOfAccount->delete();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }
}
