<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountCollection;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountCollection
     */
    public function index()
    {
        return new ChartOfAccountCollection(ChartOfAccount::orderBy('type_id')->orderBy('number')->orderBy('alias')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountResource
     */
    public function update(Request $request, $id)
    {
        $chartOfAccount = ChartOfAccount::findOrFail($id);
        $chartOfAccount->type_id = $request->get('type_id');
        $chartOfAccount->number = $request->get('number') ?? null;
        $chartOfAccount->name = $request->get('name');
        $chartOfAccount->alias = $request->get('name');
        $chartOfAccount->save();

        return new ChartOfAccountResource($chartOfAccount);
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
