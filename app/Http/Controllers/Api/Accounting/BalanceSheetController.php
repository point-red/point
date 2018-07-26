<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Resources\Accounting\BalanceSheet\BalanceSheetCollection;
use App\Http\Resources\Accounting\ChartOfAccount\ChartOfAccountCollection;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BalanceSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Accounting\BalanceSheet\BalanceSheetCollection
     */
    public function index()
    {
        return new BalanceSheetCollection(ChartOfAccount::orderBy('type_id')->orderBy('number')->orderBy('alias')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
