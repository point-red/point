<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransferItem\StoreReceiveItemRequest;
use Illuminate\Http\Request;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\ReceiveItem;
use Illuminate\Support\Facades\DB;

class ReceiveItemController extends Controller
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
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - warehouse_id (Int)
     *  - from_warehouse_id (Int)
     *  - transfer_item_id (Int)
     *  - driver (String)
     *  -
     *  - items (Array) :
     *      - item_id (Int)
     *      - item_name (String)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *
     * @param StoreReceiveItemRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreReceiveItemRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $transferItem = ReceiveItem::create($request->all());
            $transferItem
                ->load('form')
                ->load('warehouse')
                ->load('items.item');

            return new ApiResource($transferItem);
        });

        return $result;
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
