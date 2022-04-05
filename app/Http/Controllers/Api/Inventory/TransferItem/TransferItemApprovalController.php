<?php

namespace App\Http\Controllers\Api\Inventory\TransferItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\UserActivity;
use App\Model\Token;
use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Journal\JournalHelper;
use App\Model\Master\User;
use App\Mail\TransferItemApprovalRequestSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TransferItemApprovalController extends Controller
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
        $transferItems = TransferItem::join('forms', 'forms.formable_id', '=', TransferItem::getTableName().'.id')
            ->where(['forms.formable_type' => TransferItem::$morphName, 'forms.approval_status' => 0])
            ->whereNotNull('number')
            ->whereRaw("(number like '%" . $filter_like . "%' 
                or w1.name like '%" . $filter_like . "%'
                or w2.name like '%" . $filter_like . "%'
                or date like '%" . $filter_like . "%')")
            ->join('warehouses as w1', 'w1.id', '=', TransferItem::getTableName().'.warehouse_id')
            ->join('warehouses as w2', 'w2.id', '=', TransferItem::getTableName().'.to_warehouse_id')
            ->select('transfer_items.id', 'date', 'number', 'w1.name as warehouse_send', 'w2.name as warehouse_receive', 'last_request_date')
            ->orderBy('date', 'desc');

        $user_request = UserActivity::where(['activity' => 'Request Approval', 'table_type' => 'forms'])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $transferItems = $transferItems->leftJoinSub($user_request, 'user_request', function ($q) {
            $q->on('forms.id', '=', 'user_request.table_id');
        });
        
        $transferItems = pagination($transferItems, $request->get('limit'));

        return new ApiCollection($transferItems);
    }
    
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
    
        $transferItem = TransferItem::findOrFail($id);
        if ($transferItem->form->approval_status === 0) {
            $transferItem->form->approval_by = auth()->user()->id;
            $transferItem->form->approval_at = now();
            $transferItem->form->approval_status = 1;
            $transferItem->form->save();

            TransferItem::updateInventory($transferItem->form, $transferItem);
            TransferItem::updateJournal($transferItem);
        }

        DB::connection('tenant')->commit();

        return new ApiResource($transferItem);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $transferItem = TransferItem::findOrFail($id);
        $transferItem->form->approval_by = auth()->user()->id;
        $transferItem->form->approval_at = now();
        $transferItem->form->approval_reason = $request->get('reason');
        $transferItem->form->approval_status = -1;
        $transferItem->form->save();

        return new ApiResource($transferItem);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        $transferItems = TransferItem::whereIn('id', $request->ids)->get();

        foreach ($transferItems as $transferItem) { 
            $datas[$transferItem->form->request_approval_to][] = $transferItem;
        }

        foreach ($datas as $data) {
            $no = 1;
            $ids = '';
            foreach ($data as $transferItem) {
                $transferItem['no'] = $no;
                $transferItem['created_by'] = User::findOrFail($transferItem->form->created_by)->getFullNameAttribute();
                if ($transferItem->form->cancellation_status === 0) {
                    $transferItem['action'] = 'delete';
                } else if ($transferItem->form->cancellation_status === null and $transferItem->form->approval_status === 0) {
                    $userActivity = UserActivity::where('number', $transferItem->form->number);
                    $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
                    $updateCount = $userActivity->count();
                    if ($updateCount > 0) {
                        $transferItem['action'] = 'update';
                    } else {
                        $transferItem['action'] = 'create';
                    }
                }
                $no++;
                $ids .= $transferItem->id . ',';
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
            ])->queue(new TransferItemApprovalRequestSent(
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
