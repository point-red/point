<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Accounting\ChartOfAccount;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountCollection;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountCollection
     */
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::eloquentFilter($request);

        // Filter account by type
        // ex : filter_type = 'cash,bank'
        if ($request->has('filter_type')) {
            $types = explode(',', $request->get('filter_type'));
            $accounts->whereHas('type', function ($query) use ($types) {
                $query->whereIn('name', $types);
            });
        }

        $accounts = pagination($accounts, $request->get('limit'));

        return new ChartOfAccountCollection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request  $request
     * @return \App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource
     */
    public function store(Request $request)
    {
        $chartOfAccount = new ChartOfAccount;
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('name');
        $chartOfAccount->save();

        return new ChartOfAccountResource($chartOfAccount);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id)->load(['type', 'group']);

        return new ApiResource($chartOfAccount);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id);
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('name');
        $chartOfAccount->save();

        return new ApiResource($chartOfAccount->load(['type', 'group']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource
     */
    public function destroy($id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id);

        $chartOfAccount->delete();

        return new ChartOfAccountResource($chartOfAccount);
    }
}
