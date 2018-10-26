<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Resources\ApiCollection;
use App\Model\Accounting\Journal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        // Total Account Payable
        // Option = all / settled / unsettled
        $request->get('status');
        // Account Payable aging (days)
        $request->get('age');
        $request->get('supplier_id');

        $journals = Journal::all();

        return new ApiCollection($journals);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function show(Request $request)
    {
        // Total Account Payable
        // Option = all / settled / unsettled
        $request->get('status');
        // Account Payable aging (days)
        $request->get('age');
        $request->get('type'); // supplier / employee / expedition
        $request->get('form_number');

        $journals = Journal::all();

        return new ApiCollection($journals);
    }
}
