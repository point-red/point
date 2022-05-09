<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\TransferItem;
use Illuminate\Support\Facades\DB;

class ReceiveItemApprovalController extends Controller
{   
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $receiveItem = ReceiveItem::findOrFail($id);
        if ($receiveItem->form->approval_status === 0) {
            $receiveItem->form->approval_by = auth()->user()->id;
            $receiveItem->form->approval_at = now();
            $receiveItem->form->approval_status = 1;
            $receiveItem->form->save();
            
            ReceiveItem::updateInventory($receiveItem->form, $receiveItem);
            ReceiveItem::updateJournal($receiveItem);

            $transferItem = TransferItem::findOrFail($receiveItem->transfer_item_id);
            $transferItem->form->done = $request->form_send_done;
            $transferItem->form->save();
        }

        DB::connection('tenant')->commit();

        return new ApiResource($receiveItem);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $receiveItem = ReceiveItem::findOrFail($id);
        $receiveItem->form->approval_by = auth()->user()->id;
        $receiveItem->form->approval_at = now();
        $receiveItem->form->approval_reason = $request->get('reason');
        $receiveItem->form->approval_status = -1;
        $receiveItem->form->save();

        return new ApiResource($receiveItem);
    }
}
