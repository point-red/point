<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\ReceiveItem;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\UserActivity;
use App\Model\Token;
use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Journal\JournalHelper;
use Illuminate\Support\Facades\DB;

class ReceiveItemApprovalByEmailController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request)
    {
        DB::connection('tenant')->beginTransaction();

        $token = Token::where('user_id', $request->approver_id)->where('token', $request->token)->first();
        
        if (!$token) {
            return response()->json([
                'code' => 422,
                'message' => 'Approve email failed',
                'errors' => [],
            ], 422);
        };

        $receiveItem = ReceiveItem::findOrFail($request->ids[0]);
        
        if ($receiveItem->form->cancellation_status === 0) {
            $receiveItem->form->cancellation_approval_by = $request->approver_id;
            $receiveItem->form->cancellation_approval_at = now();
            $receiveItem->form->cancellation_status = 1;
            $receiveItem->form->save();

            JournalHelper::delete($receiveItem->form->id);
            InventoryHelper::delete($receiveItem->form->id);

            $transferItem = TransferItem::findOrFail($receiveItem->transfer_item_id);
            $transferItem->form->done = 0;
            $transferItem->form->save();

            // Insert User Activity
            $userActivity = new UserActivity;
            $userActivity->table_type = 'forms';
            $userActivity->table_id = $receiveItem->form->id;
            $userActivity->number = $receiveItem->form->number;
            $userActivity->date = now();
            $userActivity->user_id = $request->approver_id;
            $userActivity->activity = 'Cancellation Approved by Email';
            $userActivity->save();

        } else if ($receiveItem->form->approval_status === 0) {
            $receiveItem->form->approval_by = $request->approver_id;
            $receiveItem->form->approval_at = now();
            $receiveItem->form->approval_status = 1;
            $receiveItem->form->save();

            ReceiveItem::updateInventory($receiveItem->form, $receiveItem);
            ReceiveItem::updateJournal($receiveItem);

            $transferItem = TransferItem::findOrFail($receiveItem->transfer_item_id);
            $transferItem->form->done = $request->form_send_done;
            $transferItem->form->save();

            // Insert User Activity
            $userActivity = new UserActivity;
            $userActivity->table_type = 'forms';
            $userActivity->table_id = $receiveItem->form->id;
            $userActivity->number = $receiveItem->form->number;
            $userActivity->date = now();
            $userActivity->user_id = $request->approver_id;
            $userActivity->activity = 'Approved by Email';
            $userActivity->save();
        }

        DB::connection('tenant')->commit();

        return new ApiResource($receiveItem);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request)
    {
        $token = Token::where('user_id', $request->approver_id)->where('token', $request->token)->first();

        if (!$token) {
            return response()->json([
                'code' => 422,
                'message' => 'Approve email failed',
                'errors' => [],
            ], 422);
        };
        
        $receiveItem = ReceiveItem::findOrFail($request->ids[0]);
        
        if ($receiveItem->form->cancellation_status === 0) {
            $receiveItem->form->cancellation_approval_by = $request->approver_id;
            $receiveItem->form->cancellation_approval_at = now();
            $receiveItem->form->cancellation_approval_reason = $request->get('reason');
            $receiveItem->form->cancellation_status = -1;
            $receiveItem->form->save();

            // Insert User Activity
            $userActivity = new UserActivity;
            $userActivity->table_type = 'forms';
            $userActivity->table_id = $receiveItem->form->id;
            $userActivity->number = $receiveItem->form->number;
            $userActivity->date = now();
            $userActivity->user_id = $request->approver_id;
            $userActivity->activity = 'Cancellation Rejected by Email';
            $userActivity->save();

        } else if ($receiveItem->form->approval_status === 0) {
            $receiveItem->form->approval_by = $request->approver_id;
            $receiveItem->form->approval_at = now();
            $receiveItem->form->approval_reason = $request->get('reason');
            $receiveItem->form->approval_status = -1;
            $receiveItem->form->save();

            // Insert User Activity
            $userActivity = new UserActivity;
            $userActivity->table_type = 'forms';
            $userActivity->table_id = $receiveItem->form->id;
            $userActivity->number = $receiveItem->form->number;
            $userActivity->date = now();
            $userActivity->user_id = $request->approver_id;
            $userActivity->activity = 'Rejected by Email';
            $userActivity->save();
        }

        DB::connection('tenant')->commit();

        return new ApiResource($receiveItem);
    }
}
