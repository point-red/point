<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\UserActivity;
use App\Model\Token;
use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Journal\JournalHelper;
use Illuminate\Support\Facades\DB;
use App\Model\Master\Item;

class TransferItemApprovalByEmailController extends Controller
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
    
        $transferItems = TransferItem::whereIn('id', $request->ids)->get();

        foreach ($transferItems as $transferItem) {
            $exclude = [];
            // check if stock is enough to prevent stock minus
            foreach ($transferItem->items as $line) {
                $item = Item::where('id', $line->item_id)->first();
               
                $stock = InventoryHelper::getCurrentStock($item, $transferItem->form->date, $transferItem->warehouse, [
                    'expiry_date' => $line->expiry_date,
                    'production_number' => $line->production_number,
                ]);
                
                if (abs($line->quantity) > $stock) {
                    array_push($exclude, $transferItem->id);
                }
            }

            if (!in_array( $transferItem->id, $exclude )) {
                if ($transferItem->form->cancellation_status === 0) {
                    $transferItem->form->cancellation_approval_by = $request->approver_id;
                    $transferItem->form->cancellation_approval_at = now();
                    $transferItem->form->cancellation_status = 1;
                    $transferItem->form->save();
    
                    JournalHelper::delete($transferItem->form->id);
                    InventoryHelper::delete($transferItem->form->id);
    
                    // Insert User Activity
                    $userActivity = new UserActivity;
                    $userActivity->table_type = 'forms';
                    $userActivity->table_id = $transferItem->form->id;
                    $userActivity->number = $transferItem->form->number;
                    $userActivity->date = now();
                    $userActivity->user_id = $request->approver_id;
                    $userActivity->activity = 'Cancellation Approved by Email';
                    $userActivity->save();
    
                } else if ($transferItem->form->approval_status === 0) {
                    $transferItem->form->approval_by = $request->approver_id;
                    $transferItem->form->approval_at = now();
                    $transferItem->form->approval_status = 1;
                    $transferItem->form->save();
        
                    TransferItem::updateInventory($transferItem->form, $transferItem);
                    TransferItem::updateJournal($transferItem);
    
                    // Insert User Activity
                    $userActivity = new UserActivity;
                    $userActivity->table_type = 'forms';
                    $userActivity->table_id = $transferItem->form->id;
                    $userActivity->number = $transferItem->form->number;
                    $userActivity->date = now();
                    $userActivity->user_id = $request->approver_id;
                    $userActivity->activity = 'Approved by Email';
                    $userActivity->save();
                }
            }
    
            DB::connection('tenant')->commit();
        }


        return new ApiResource($transferItems);
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
        
        $transferItems = TransferItem::whereIn('id', $request->ids)->get();
        
        foreach ($transferItems as $transferItem) {
            if ($transferItem->form->cancellation_status === 0) {
                $transferItem->form->cancellation_approval_by = $request->approver_id;
                $transferItem->form->cancellation_approval_at = now();
                $transferItem->form->cancellation_approval_reason = $request->get('reason');
                $transferItem->form->cancellation_status = -1;
                $transferItem->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $transferItem->form->id;
                $userActivity->number = $transferItem->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Cancellation Rejected by Email';
                $userActivity->save();

            } else if ($transferItem->form->approval_status === 0) {
                $transferItem->form->approval_by = $request->approver_id;
                $transferItem->form->approval_at = now();
                $transferItem->form->approval_reason = $request->get('reason');
                $transferItem->form->approval_status = -1;
                $transferItem->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $transferItem->form->id;
                $userActivity->number = $transferItem->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Rejected by Email';
                $userActivity->save();
            }
        }

        DB::connection('tenant')->commit();

        return new ApiResource($transferItems);
    }
}
