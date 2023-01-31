<?php

namespace App\Http\Controllers\Api\Sales\SalesReturn;

use Throwable;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\SalesReturn\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Sales\SalesReturn\SalesReturn\StoreSalesReturnRequest;
use App\Http\Requests\Sales\SalesReturn\SalesReturn\UpdateSalesReturnRequest;
use App\Http\Requests\Sales\SalesReturn\SalesReturn\DeleteSalesReturnRequest;
use Exception;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $salesReturn = SalesReturn::from(SalesReturn::getTableName().' as '.SalesReturn::$alias)->eloquentFilter($request);

        $salesReturn = SalesReturn::joins($salesReturn, $request->get('join'));

        $salesReturn = pagination($salesReturn, $request->get('limit'));

        return new ApiCollection($salesReturn);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSalesReturnRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesReturnRequest $request)
    {
        try {
            $result = DB::connection('tenant')->transaction(function () use ($request) {
                $salesReturn = SalesReturn::create($request->all());
                $salesReturn
                    ->load('form')
                    ->load('items');
    
                return new ApiResource($salesReturn);
            });
        }  catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 422);
        }        

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $salesReturn = SalesReturn::eloquentFilter($request)->findOrFail($id);

        if ($request->has('with_archives')) {
            $salesReturn->archives = $salesReturn->archives();
        }

        return new ApiResource($salesReturn);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateSalesReturnRequest  $request
     * @param  int  $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateSalesReturnRequest $request, $id)
    {
        $salesReturn = SalesReturn::with('form')->findOrFail($id);

        try {
            
            $salesReturn->isAllowedToUpdate();

            $result = DB::connection('tenant')->transaction(function () use ($request, $salesReturn) {
                $salesReturn->form->archive($request->notes);
                if ($salesReturn->form->approval_status === 1) {
                    SalesReturn::updateInvoiceQuantity($salesReturn, 'revert');
                }                
                $request['number'] = $salesReturn->form->edited_number;
                $request['old_increment'] = $salesReturn->form->increment;

                $salesReturn = SalesReturn::create($request->all());
                $salesReturn->load(['form', 'customer', 'items']);

                return new ApiResource($salesReturn);
            });
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 422);
        }

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $salesReturn = SalesReturn::findOrFail($id);
        $salesReturn->isAllowedToDelete();

        $request->validate([ 'reason' => 'required']);
        
        $salesReturn->requestCancel($request);

        return response()->json([], 204);

        
    }
}
