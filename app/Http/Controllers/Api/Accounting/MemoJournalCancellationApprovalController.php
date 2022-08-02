<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Helpers\Journal\JournalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\MemoJournal;
use Illuminate\Http\Request;

class MemoJournalCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
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
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $memoJournal = MemoJournal::findOrFail($id);
        $memoJournal->form->cancellation_approval_by = auth()->user()->id;
        $memoJournal->form->cancellation_approval_at = now();
        $memoJournal->form->cancellation_approval_reason = $request->get('reason');
        $memoJournal->form->cancellation_status = -1;
        $memoJournal->form->save();

        return new ApiResource($memoJournal);
    }
}
