<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\TransferItem;
use Illuminate\Http\Request;

class TransferItemCloseApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $transferItem = TransferItem::findOrFail($id);
        
        TransferItem::closeForm($transferItem, $request->items);
        
        $transferItem->form->close_approval_by = auth()->user()->id;
        $transferItem->form->close_approval_at = now();
        $transferItem->form->close_status = 1;
        $transferItem->form->done = 1;
        $transferItem->form->save();

        return new ApiResource($transferItem);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $transferItem = transferItem::findOrFail($id);
        $transferItem->form->cancellation_approval_by = auth()->user()->id;
        $transferItem->form->cancellation_approval_at = now();
        $transferItem->form->cancellation_approval_reason = $request->get('reason');
        $transferItem->form->cancellation_status = -1;
        $transferItem->form->save();

        return new ApiResource($transferItem);
    }
}
