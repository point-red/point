<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreRequest;
use App\Http\Resources\Accounting\CutOff\CutOffResource;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\Journal;
use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CutOffController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function store(StoreRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        // cannot have more than one cutoff in single day
        if (CutOff::all()->count() > 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'cutoff already exists'
            ], 422);
        }

        $cutOff = new CutOff;
        $cutOff->save();

        $form = new Form;
        $form->saveData($request->all(), $cutOff, ['auto_approve' => false]);

//        $details = $request->get('details');
//        for ($i = 0; $i < count($details); $i++) {
//            $cutOffAccount = new CutOffAccount;
//            $cutOffAccount->cut_off_id = $cutOff->id;
//            $cutOffAccount->chart_of_account_id = $request->get('details')[$i]['id'];
//            $cutOffAccount->debit = $request->get('details')[$i]['debit'] ?? 0;
//            $cutOffAccount->credit = $request->get('details')[$i]['credit'] ?? 0;
//            $cutOffAccount->save();

//            $journal = new Journal;
//            $journal->form_id = $form->id;
//            $journal->chart_of_account_id = $cutOffAccount->chart_of_account_id;
//            $journal->debit = $cutOffAccount->debit;
//            $journal->credit = $cutOffAccount->credit;
//            $journal->save();
//        }

        DB::connection('tenant')->commit();

        return new CutOffResource($cutOff);
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
        $cutOff = CutOff::eloquentFilter($request)->with('form.createdBy')->findOrFail($id);

        if ($request->has('with_archives')) {
            $cutOff->archives = $cutOff->archives();
        }

        if ($request->has('with_origin')) {
            $cutOff->origin = $cutOff->origin();
        }

        return new ApiResource($cutOff);
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
     *
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOff = CutOff::findOrFail($id);

        $cutOff->delete();

        Journal::where('journalable_type', CutOff::class)->where('journalable_id', $id)->delete();

        DB::connection('tenant')->commit();

        return new CutOffResource($cutOff);
    }
}
