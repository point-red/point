<?php

namespace App\Http\Controllers\Api\Finance\CashAdvance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CashAdvance\StoreCashAdvanceRequest;
use App\Http\Requests\Finance\CashAdvance\UpdateCashAdvanceRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Finance\CashAdvance\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class CashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cashAdvance = CashAdvance::from(CashAdvance::getTableName().' as '.CashAdvance::$alias)->eloquentFilter($request);

        $cashAdvance = CashAdvance::joins($cashAdvance, $request->get('join'));

        $cashAdvance = pagination($cashAdvance, $request->get('limit'));

        return new ApiCollection($cashAdvance);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCashAdvanceRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreCashAdvanceRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $cashAdvance = CashAdvance::create($request->all());
            $cashAdvance->mapHistory($cashAdvance->form, $request->all());
            $cashAdvance
                ->load('form')
                ->load('details.account')
                ->load('employee');

            return new ApiResource($cashAdvance);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $cashAdvance = CashAdvance::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($cashAdvance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCashAdvanceRequest $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function update(UpdateCashAdvanceRequest $request, $id)
    {
        
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->mapHistory($cashAdvance->form, $request->all());
        $cashAdvance->archive();

        $result = DB::connection('tenant')->transaction(function () use ($request, $cashAdvance) {

            $cashAdvanceNew = CashAdvance::create($request->all());
            $cashAdvanceNew->form->increment = $cashAdvance->form->increment;
            $cashAdvanceNew->form->save();

            $cashAdvanceNew
                ->load('form')
                ->load('details.account')
                ->load('employee');

            return new ApiResource($cashAdvanceNew);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->isAllowedToDelete();

        $response = $cashAdvance->requestCancel($request);

        $cashAdvance->mapHistory($cashAdvance->form, $request->all());

        return response()->json([], 204);
    }
}
