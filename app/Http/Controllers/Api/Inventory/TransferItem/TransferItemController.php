<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransferItem\StoreTransferItemRequest;
use App\Http\Requests\Inventory\TransferItem\UpdateTransferItemRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Exports\TransferItem\TransferItemSendExport;
use App\Model\CloudStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransferItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $transferItems = TransferItem::from(TransferItem::getTableName().' as '.TransferItem::$alias)->eloquentFilter($request);
        
        $transferItems = TransferItem::joins($transferItems, $request->get('join'));
        
        $transferItems = pagination($transferItems, $request->get('limit'));
        
        return new ApiCollection($transferItems);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - warehouse_id (Int)
     *  - to_warehouse_id (Int)
     *  - driver (String)
     *  -
     *  - items (Array) :
     *      - item_id (Int)
     *      - item_name (String)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *
     * @param StoreTransferItemRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreTransferItemRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $transferItem = TransferItem::create($request->all());
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
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $transferItem = TransferItem::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($transferItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransferItemRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateTransferItemRequest $request, $id)
    {
        $transferItem = TransferItem::findOrFail($id);
        $transferItem->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $transferItem) {
            $transferItem->form->archive();
            $request['number'] = $transferItem->form->edited_number;
            $request['old_increment'] = $transferItem->form->increment;

            $transferItem = TransferItem::create($request->all());
            $transferItem
                ->load('form')
                ->load('warehouse')
                ->load('items.item');

            return new ApiResource($transferItem);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $transferItem = TransferItem::findOrFail($id);
        $transferItem->isAllowedToDelete();
        $transferItem->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }

    /**
     * Close Form the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function close(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $transferItem = TransferItem::findOrFail($id);

        $transferItem->form->request_close_to = $transferItem->form->request_approval_to;
        $transferItem->form->request_close_by = tenant(auth()->user()->id)->id;
        $transferItem->form->request_close_at = now();
        $transferItem->form->request_close_reason = $request->get('data')['reason'];
        $transferItem->form->close_status = false;
        $transferItem->form->save();

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }

    public function export(Request $request)
    {
        $request->validate([
            'data' => 'required',
        ]);
        
        $tenant = strtolower($request->header('Tenant'));

        $dateForm = date('d F Y', strtotime($request->data['date_start']));
        $dateTo = date('d F Y', strtotime($request->data['date_end']));
        
        $key = Str::random(16);
        $fileName = 'Transfer Item Send_'.$dateForm.'-'.$dateTo;
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        Excel::store(new TransferItemSendExport($request->data['date_start'], $request->data['date_end'], $request->data['ids'], $request->data['tenant_name']), $path, env('STORAGE_DISK'));

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'transfer item send';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->owner_id = auth()->user()->id;
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }
}
