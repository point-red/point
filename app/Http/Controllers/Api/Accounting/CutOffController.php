<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\Journal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Model\Accounting\CutOffDetail;
use App\Http\Resources\Accounting\CutOff\CutOffResource;
use App\Http\Resources\Accounting\CutOff\CutOffCollection;

class CutOffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\Accounting\CutOff\CutOffCollection
     */
    public function index()
    {
        return new CutOffCollection(CutOff::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function store(Request $request)
    {
        DB::connection('tenant')->beginTransaction();

        $fromDate = date('Y-m-01 00:00:00', strtotime($request->get('date')));
        $untilDate = date('Y-m-t 23:59:59', strtotime($request->get('date')));
        $increment = CutOff::where('date', '>=', $fromDate)->where('date', '<=', $untilDate)->count();

        $cutOff = new CutOff;
        $cutOff->date = $request->get('date');
        $cutOff->code = 'CUTOFF/'.date('ym', strtotime($request->get('date'))).'/'.sprintf('%04d', ++$increment);
        $cutOff->save();

        $details = $request->get('details');
        for ($i = 0; $i < count($details); $i++) {
            $cutOffDetail = new CutOffDetail;
            $cutOffDetail->cut_off_id = $cutOff->id;
            $cutOffDetail->chart_of_account_id = $request->get('details')[$i]['id'];
            $cutOffDetail->debit = $request->get('details')[$i]['debit'] ?? 0;
            $cutOffDetail->credit = $request->get('details')[$i]['credit'] ?? 0;
            $cutOffDetail->save();

            $journal = new Journal;
            $journal->journalable_type = get_class(new CutOff());
            $journal->journalable_id = $cutOff->id;
            $journal->date = $cutOff->date;
            $journal->chart_of_account_id = $cutOffDetail->chart_of_account_id;
            $journal->debit = $cutOffDetail->debit;
            $journal->credit = $cutOffDetail->credit;
            $journal->save();
        }

        DB::connection('tenant')->commit();

        return new CutOffResource($cutOff);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function show($id)
    {
        return new CutOffResource(CutOff::findOrFail($id));
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
