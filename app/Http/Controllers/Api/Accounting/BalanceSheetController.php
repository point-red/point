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
}
