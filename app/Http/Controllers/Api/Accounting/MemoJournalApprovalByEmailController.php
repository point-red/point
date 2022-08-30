<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\MemoJournal;
use App\Model\UserActivity;
use App\Model\Token;
use App\Helpers\Journal\JournalHelper;
use Illuminate\Support\Facades\DB;

class MemoJournalApprovalByEmailController extends Controller
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
    
        $memoJournals = MemoJournal::whereIn('id', $request->ids)->get();

        foreach ($memoJournals as $memoJournal) {

            if ($memoJournal->form->cancellation_status === 0) {
                $memoJournal->form->cancellation_approval_by = $request->approver_id;
                $memoJournal->form->cancellation_approval_at = now();
                $memoJournal->form->cancellation_status = 1;
                $memoJournal->form->save();

                JournalHelper::delete($memoJournal->form->id);

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $memoJournal->form->id;
                $userActivity->number = $memoJournal->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Cancellation Approved by Email';
                $userActivity->save();

            } else if ($memoJournal->form->approval_status === 0) {
                $memoJournal->form->approval_by = $request->approver_id;
                $memoJournal->form->approval_at = now();
                $memoJournal->form->approval_status = 1;
                $memoJournal->form->save();
    
                MemoJournal::updateJournal($memoJournal);

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $memoJournal->form->id;
                $userActivity->number = $memoJournal->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Approved by Email';
                $userActivity->save();
            }
    
            DB::connection('tenant')->commit();
        }


        return new ApiResource($memoJournals);
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
                'message' => 'Reject email failed',
                'errors' => [],
            ], 422);
        };
        
        $memoJournals = MemoJournal::whereIn('id', $request->ids)->get();
        
        foreach ($memoJournals as $memoJournal) {
            if ($memoJournal->form->cancellation_status === 0) {
                $memoJournal->form->cancellation_approval_by = $request->approver_id;
                $memoJournal->form->cancellation_approval_at = now();
                $memoJournal->form->cancellation_approval_reason = $request->get('reason');
                $memoJournal->form->cancellation_status = -1;
                $memoJournal->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $memoJournal->form->id;
                $userActivity->number = $memoJournal->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Cancellation Rejected by Email';
                $userActivity->save();

            } else if ($memoJournal->form->approval_status === 0) {
                $memoJournal->form->approval_by = $request->approver_id;
                $memoJournal->form->approval_at = now();
                $memoJournal->form->approval_reason = $request->get('reason');
                $memoJournal->form->approval_status = -1;
                $memoJournal->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $memoJournal->form->id;
                $userActivity->number = $memoJournal->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Rejected by Email';
                $userActivity->save();
            }
        }

        DB::connection('tenant')->commit();

        return new ApiResource($memoJournals);
    }
}
