<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ChartOfAccount\StoreRequest;
use App\Http\Requests\Accounting\ChartOfAccount\UpdateRequest;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::from('chart_of_accounts as '.ChartOfAccount::$alias)->eloquentFilter($request);

        $accounts = ChartOfAccount::joins($accounts, $request->get('join'));

        if ($request->get('is_archived')) {
            $accounts = $accounts->whereNotNull('account.archived_at');
        } else {
            $accounts = $accounts->whereNull('account.archived_at');
        }

        $accounts = pagination($accounts, $request->get('limit'));

        return new ApiCollection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ChartOfAccountResource
     */
    public function store(StoreRequest $request)
    {
        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->sub_ledger = $request->get('sub_ledger') ?? null;
        $chartOfAccount->position = $request->get('position');
        $chartOfAccount->cash_flow = $request->get('cash_flow');
        if ($request->get('cash_flow')) {
            $chartOfAccount->cash_flow_position = $request->get('cash_flow_position') ?? null;
        }
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('name');
        $chartOfAccount->save();

        return new ChartOfAccountResource($chartOfAccount);
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
        $account = ChartOfAccount::from('chart_of_accounts as '.ChartOfAccount::$alias)->eloquentFilter($request);

        $account = ChartOfAccount::joins($account, $request->get('join'));

        $account = $account->where(ChartOfAccount::$alias.'.id', $id)->first();

        return new ApiResource($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(UpdateRequest $request, $id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id);
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->sub_ledger = $request->get('sub_ledger') ?? null;
        $chartOfAccount->position = $request->get('position');
        $chartOfAccount->cash_flow = $request->get('cash_flow');
        if ($request->get('cash_flow')) {
            $chartOfAccount->cash_flow_position = $request->get('cash_flow_position') ?? null;
        }
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->alias = $request->get('alias');
        $chartOfAccount->save();

        return new ApiResource($chartOfAccount->load(['type', 'group']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return ChartOfAccountResource
     */
    public function destroy($id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id);

        if (! $chartOfAccount->is_locked) {
            $chartOfAccount->delete();
        }

        return new ChartOfAccountResource($chartOfAccount);
    }
}
