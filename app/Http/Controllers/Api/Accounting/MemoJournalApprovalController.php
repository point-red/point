<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use App\Model\Accounting\MemoJournal;
use App\Model\Accounting\MemoJournalItem;
use App\Model\UserActivity;
use App\Model\Token;
use App\Model\Master\User;
use App\Mail\MemoJournalApprovalRequestSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MemoJournalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $filter_like = $request->get('filter_like');
        $memoJournals = MemoJournal::join('forms', 'forms.formable_id', '=', MemoJournal::getTableName().'.id')
            ->where(['forms.formable_type' => MemoJournal::$morphName, 'forms.approval_status' => 0])
            ->whereNotNull('number')
            ->whereRaw("(number like '%" . $filter_like . "%' 
                or users.name like '%" . $filter_like . "%'
                or forms.notes like '%" . $filter_like . "%'
                or date like '%" . $filter_like . "%')")
            ->join('users', 'users.id', '=', 'forms.created_by')
            ->select('memo_journals.id', 'date', 'number', 'forms.notes', 'users.name', 'last_request_date')
            ->orderBy('date', 'desc');

        $user_request = UserActivity::where(['activity' => 'Request Approval', 'table_type' => 'forms'])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $memoJournals = $memoJournals->leftJoinSub($user_request, 'user_request', function ($q) {
            $q->on('forms.id', '=', 'user_request.table_id');
        });
        
        $memoJournals = pagination($memoJournals, $request->get('limit'));
        
        foreach ($memoJournals as $memoJournal) {
            $memoJournal->items = MemoJournalItem::where('memo_journal_id', $memoJournal->id)->get();
        };

        return new ApiCollection($memoJournals);
    }
    
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
    
        $memoJournal = MemoJournal::findOrFail($id);
        if ($memoJournal->form->approval_status === 0) {
            $memoJournal->form->approval_by = auth()->user()->id;
            $memoJournal->form->approval_at = now();
            $memoJournal->form->approval_status = 1;
            $memoJournal->form->save();

            MemoJournal::updateJournal($memoJournal);
        }

        DB::connection('tenant')->commit();

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
        $memoJournal->form->approval_by = auth()->user()->id;
        $memoJournal->form->approval_at = now();
        $memoJournal->form->approval_reason = $request->get('reason');
        $memoJournal->form->approval_status = -1;
        $memoJournal->form->save();

        return new ApiResource($memoJournal);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        $memoJournals = MemoJournal::whereIn('id', $request->ids)->get();
        
        foreach ($memoJournals as $memoJournal) { 
            $datas[$memoJournal->form->request_approval_to][] = $memoJournal;
        }

        foreach ($datas as $data) {
            $no = 1;
            $ids = '';
            foreach ($data as $memoJournal) {
                $memoJournal['no'] = $no;
                $memoJournal['created_by'] = User::findOrFail($memoJournal->form->created_by)->getFullNameAttribute();
                if ($memoJournal->form->cancellation_status === 0) {
                    $memoJournal['action'] = 'delete';
                } else if ($memoJournal->form->cancellation_status === null and $memoJournal->form->approval_status === 0) {
                    $userActivity = UserActivity::where('number', $memoJournal->form->number);
                    $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
                    $updateCount = $userActivity->count();
                    if ($updateCount > 0) {
                        $memoJournal['action'] = 'update';
                    } else {
                        $memoJournal['action'] = 'create';
                    }
                }
                $no++;
                $ids .= $memoJournal->id . ',';
            }
            $ids = substr($ids, 0, -1);
            $approver = User::findOrFail($data[0]->form->request_approval_to);

            // create token based on request_approval_to
            $token = Token::where('user_id', $approver->id)->first();

            if (!$token) {
                $token = new Token([
                    'user_id' => $approver->id,
                    'token' => md5($approver->email.''.now()),
                ]);
                $token->save();
            }

            if (count($data) > 1) {
                $form['number'] = $data[0]->form->number . ' - ' . end($data)->form->number;
                $form['date'] = date('d F Y', strtotime($data[0]->form->date)) . ' - ' . date('d F Y', strtotime(end($data)->form->date));
                $form['created'] = date('d F Y H:i:s', strtotime($data[0]->form->created_at)) . ' - ' . date('d F Y H:i:s', strtotime(end($data)->form->created_at));
            } else {
                $form['number'] = $data[0]->form->number;
                $form['date'] = $data[0]->form->date;
                $form['created'] = $data[0]->form->created_at;
            };

            DB::connection('tenant')->commit();

            Mail::to([
                $approver->email,
            ])->queue(new MemoJournalApprovalRequestSent(
                $data,
                $approver,
                $form,
                $_SERVER['HTTP_REFERER'],
                $ids,
                $token->token
            ));
        }

        return [
            'input' => $request->all(),
        ];
    }
}
