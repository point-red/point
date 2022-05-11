<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Journal\JournalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\TransferItem;
use Illuminate\Http\Request;

class ReceiveItemCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $receiveItem = ReceiveItem::findOrFail($id);
        $receiveItem->form->cancellation_approval_by = auth()->user()->id;
        $receiveItem->form->cancellation_approval_at = now();
        $receiveItem->form->cancellation_status = 1;
        $receiveItem->form->save();

        JournalHelper::delete($receiveItem->form->id);
        InventoryHelper::delete($receiveItem->form->id);
        
        $transferItem = TransferItem::findOrFail($receiveItem->transfer_item_id);
        $transferItem->form->done = 0;
        $transferItem->form->save();
        
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
        $receiveItem->form->cancellation_approval_by = auth()->user()->id;
        $receiveItem->form->cancellation_approval_at = now();
        $receiveItem->form->cancellation_approval_reason = $request->get('reason');
        $receiveItem->form->cancellation_status = -1;
        $receiveItem->form->save();

        return new ApiResource($receiveItem);
    }
}
