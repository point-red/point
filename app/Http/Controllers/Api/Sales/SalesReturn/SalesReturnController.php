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
                    ->load('customer')
                    ->load('items');
    
                return new ApiResource($salesReturn);
            });
        }  catch (\Throwable $th) {
            error_log(json_encode($th));
            return response_error ($th);
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
                $request['number'] = $salesReturn->form->edited_number;
                $request['old_increment'] = $salesReturn->form->increment;

                $salesReturn = SalesReturn::create($request->all());
                $salesReturn->load(['form', 'customer', 'items']);

                return new ApiResource($salesReturn);
            });
        } catch (\Throwable $th) {
            return response_error($th);
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
