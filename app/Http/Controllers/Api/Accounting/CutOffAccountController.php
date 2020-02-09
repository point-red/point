<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreAccountRequest;
use App\Http\Requests\Accounting\CutOff\UpdateAccountRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountSubLedger;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use App\Model\Form;
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
        $cutOffs = CutOff::eloquentFilter($request);
        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));
            if (in_array('form', $fields)) {
                $cutOffs = $cutOffs->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', CutOff::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), CutOff::$morphName);
                });
            }
        }

        $cutOffs = pagination($cutOffs, $request->get('limit'));

        return new ApiCollection($cutOffs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ApiResource
     */
    public function store(StoreAccountRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        // create chart of account
        if ($request->get('sub_ledger_id')) {
            $type = ChartOfAccountType::find($request->get('type_id'));
            $subLedger = ChartOfAccountSubLedger::find($request->get('sub_ledger_id'));

            if ($subLedger->name == 'inventory' && $type->name != 'inventory') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'sub ledger "' . $subLedger->name . '" should be match with account type "inventory"',
                ], 422);
            }

            if ($subLedger->name == 'account payable' && $type->name != 'current liability') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'sub ledger "' . $subLedger->name . '" should be match with account type "current liability"',
                ], 422);
            }

            if ($subLedger->name == 'purchase down payment' && $type->name != 'current liability') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'sub ledger "' . $subLedger->name . '" should be match with account type "current liability"',
                ], 422);
            }

            if ($subLedger->name == 'account receivable' && $type->name != 'account receivable') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'sub ledger "' . $subLedger->name . '" should be match with account type "account receivable"',
                ], 422);
            }

            if ($subLedger->name == 'sales down payment' && $type->name != 'account receivable') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'sub ledger "' . $subLedger->name . '" should be match with account type "account receivable"',
                ], 422);
            }
        }

        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->sub_ledger_id = $request->get('sub_ledger_id') ?? null;
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('name');
        $chartOfAccount->save();

        // create cut off account
        $cutOffAccount = new CutOffAccount;
        $cutOffAccount->chart_of_account_id = $chartOfAccount->id;
        $cutOffAccount->cut_off_id = CutOff::where('id', '>', 0)->first()->id;
        if ($chartOfAccount->type->is_debit == true) {
            $cutOffAccount->debit = $request->get('balance');
        } else {
            $cutOffAccount->credit = $request->get('balance');
        }

        $cutOffAccount->save();

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
     * @return void
     */
    public function update(UpdateAccountRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function destroy($id)
    {
        $cutOffAccount = CutOffAccount::findOrFail($id);

        $cutOffAccount->delete();

        return new ApiResource($cutOffAccount);
    }
}
