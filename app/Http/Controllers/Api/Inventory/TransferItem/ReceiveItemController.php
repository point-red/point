<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransferItem\StoreReceiveItemRequest;
use App\Http\Requests\Inventory\TransferItem\UpdateReceiveItemRequest;
use Illuminate\Http\Request;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Master\User;
use App\Model\Token;
use App\Exports\TransferItem\ReceiveItemSendExport;
use App\Model\CloudStorage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Mail\ReceiveItemApprovalRequestSent;
use Illuminate\Support\Facades\Mail;

class ReceiveItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $receiveItems = ReceiveItem::from(ReceiveItem::getTableName().' as '.ReceiveItem::$alias)->eloquentFilter($request);
        
        $receiveItems = ReceiveItem::joins($receiveItems, $request->get('join'));
        
        $receiveItems = pagination($receiveItems, $request->get('limit'));
        
        return new ApiCollection($receiveItems);
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
            $receiveItem = ReceiveItem::create($request->all());
            $receiveItem
                ->load('form')
                ->load('warehouse')
                ->load('items.item');

            return new ApiResource($receiveItem);
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
        $receiveItem = ReceiveItem::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($receiveItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateReceiveItemRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateReceiveItemRequest $request, $id)
    {
        $receiveItem = ReceiveItem::findOrFail($id);
        
        $result = DB::connection('tenant')->transaction(function () use ($request, $receiveItem) {
            $receiveItem->form->archive();
            $request['number'] = $receiveItem->form->edited_number;
            $request['old_increment'] = $receiveItem->form->increment;
            
            $receiveItem = ReceiveItem::create($request->all());
            $receiveItem
                ->load('form')
                ->load('from_warehouse')
                ->load('items.item');

            $transferItem = TransferItem::findOrFail($receiveItem->transfer_item_id);
            $transferItem->form->done = 0;
            $transferItem->form->save();

            return new ApiResource($receiveItem);
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

        $receiveItem = ReceiveItem::findOrFail($id);
        
        $receiveItem->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $receiveItem = ReceiveItem::where('id', $id)->first();
        
        $updated_by = User::findOrFail($receiveItem->form->updated_by);
        // create token based on request_approval_to
        $token = Token::where('user_id', $receiveItem->form->requestApprovalTo->id)->first();

        if (!$token) {
            $token = new Token([
                'user_id' => $receiveItem->form->requestApprovalTo->id,
                'token' => md5($receiveItem->form->requestApprovalTo->email.''.now()),
            ]);
            $token->save();
        }
        
        DB::connection('tenant')->commit();

        Mail::to([
            $receiveItem->form->requestApprovalTo->email,
        ])->queue(new ReceiveItemApprovalRequestSent(
            $receiveItem,
            $updated_by->getFullNameAttribute(),
            $_SERVER['HTTP_REFERER'],
            $token->token,
            $request->form_send_done,
            $request->crud_type
        ));
    
        return [
            'input' => $request->all(),
        ];
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
        $fileName = 'Transfer Item Receive_'.$dateForm.'-'.$dateTo;
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        Excel::store(new ReceiveItemSendExport($request->data['date_start'], $request->data['date_end'], $request->data['ids'], $request->data['tenant_name']), $path, env('STORAGE_DISK'));

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'transfer item receive';
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
