<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Accounting\BalanceSheet\BalanceSheetCollection;
use App\Model\Accounting\ChartOfAccount;

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
}
