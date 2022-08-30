<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Helpers\Journal\JournalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Requests\Accounting\MemoJournal\ApproveMemoJournalRequest;
use App\Model\Accounting\MemoJournal;

class MemoJournalCancellationApprovalController extends Controller
{
    /**
     * @param ApproveMemoJournalRequest $request
     * @param $id
     * @return ApiResource
     */
    public function approve(ApproveMemoJournalRequest $request, $id)
    {
        $memoJournal = MemoJournal::findOrFail($id);
        $memoJournal->form->cancellation_approval_by = auth()->user()->id;
        $memoJournal->form->cancellation_approval_at = now();
        $memoJournal->form->cancellation_status = 1;
        $memoJournal->form->save();

        JournalHelper::delete($memoJournal->form->id);

        return new ApiResource($memoJournal);
    }

    /**
     * @param ApproveMemoJournalRequest $request
     * @param $id
     * @return ApiResource
     */
    public function reject(ApproveMemoJournalRequest $request, $id)
    {
        $request->validate([ 'reason' => 'required' ]);
        
        $memoJournal = MemoJournal::findOrFail($id);
        $memoJournal->form->cancellation_approval_by = auth()->user()->id;
        $memoJournal->form->cancellation_approval_at = now();
        $memoJournal->form->cancellation_approval_reason = $request->get('reason');
        $memoJournal->form->cancellation_status = -1;
        $memoJournal->form->save();

        return new ApiResource($memoJournal);
    }
}
