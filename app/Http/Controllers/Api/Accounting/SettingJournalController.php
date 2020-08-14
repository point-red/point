<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\ChartOfAccount;
use App\Model\SettingJournal;
use Illuminate\Http\Request;

class SettingJournalController extends Controller
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
     * Display the specified resource.
     *
     * @param Request $request
     * @param $feature
     * @param $name
     * @return ApiResource
     */
    public function show(Request $request, $feature, $name)
    {
        $settingJournal = SettingJournal::where('feature', $feature)->where('name', $name)->first();

        return new ApiResource($settingJournal->account);
    }
}
