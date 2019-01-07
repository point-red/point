<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return ApiResource
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $result = new \stdClass();

            if ($request->has('items')) {
                $request->validate([
                    'items' => 'required',
                ]);
            }

            if ($request->has('services')) {
                $request->validate([
                    'items' => 'required',
                ]);
            }

            if ($request->has('purchase_request')) {
                $request->validate([
                    'purchase_request.employee_id' => 'required',
                    'items' => 'required',
                ]);
                $purchaseRequest = PurchaseRequest::create($request->get('purchase_request'));
                $result->purchase_request = new ApiResource($purchaseRequest);
            }

            if ($request->has('purchase_order')) {
                $request->validate([
                    'purchase_request.supplier_id' => 'required',
                    'items' => 'required',
                ]);

                $purchaseOrder = $request->get('purchase_order');
                $purchaseOrder->purchase_request_id = $request->has('purchase_request') ? $purchaseRequest->id : null;

                $purchaseOrder = PurchaseRequest::create($purchaseOrder);
                $result->purchase_order = new ApiResource($purchaseOrder);
            }

            return $result;
        });

        return response()->json([
            'data' => $result,
        ], 201);
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
