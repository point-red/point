<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\UserActivity;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryOrders = DeliveryOrder::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)->eloquentFilter($request);

        $deliveryOrders = DeliveryOrder::joins($deliveryOrders, $request->get('join'))
            ->approvalPending();

        $userActivity = UserActivity::where(['activity' => 'Request Approval', 'table_type' => 'forms'])
            ->select('table_id')
            ->selectRaw('MAX(date) as last_request_date')
            ->groupBy('table_id');
     
        $deliveryOrders = $deliveryOrders->leftJoinSub($userActivity, 'user_activity', function ($q) {
            $q->on('form.id', '=', 'user_activity.table_id');
        });
        
        $deliveryOrders = pagination($deliveryOrders, $request->get('limit'));

        return new ApiCollection($deliveryOrders);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        
        $result = DB::connection('tenant')->transaction(function () use ($id) {
            $deliveryOrder = DeliveryOrder::findOrFail($id);

            $form = $deliveryOrder->form;
            $form->approval_by = auth()->user()->id;
            $form->approval_at = now();
            $form->approval_status = 1;
            $form->save();
    
            $salesOrder = $deliveryOrder->salesOrder;
            if ($salesOrder) {
                $salesOrder->updateStatus();
            }

            $form->fireEventApproved();
    
            return new ApiResource($deliveryOrder);
        });
        

        return $result;
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([ 'reason' => 'required' ]);

        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->form->approval_by = auth()->user()->id;
        $deliveryOrder->form->approval_at = now();
        $deliveryOrder->form->approval_reason = $validated['reason'];
        $deliveryOrder->form->approval_status = -1;
        $deliveryOrder->form->save();

        $deliveryOrder->form->fireEventRejected();

        return new ApiResource($deliveryOrder);
    }

    
    // /**
    //  * Send approval request to a specific approver.
    //  */
    // public function sendApproval(Request $request)
    // {
    //     DB::connection('tenant')->beginTransaction();
    //     $transferItems = TransferItem::whereIn('id', $request->ids)->get();

    //     foreach ($transferItems as $transferItem) { 
    //         $datas[$transferItem->form->request_approval_to][] = $transferItem;
    //     }

    //     foreach ($datas as $data) {
    //         $no = 1;
    //         $ids = '';
    //         foreach ($data as $transferItem) {
    //             $transferItem['no'] = $no;
    //             $transferItem['created_by'] = User::findOrFail($transferItem->form->created_by)->getFullNameAttribute();
    //             if ($transferItem->form->cancellation_status === 0) {
    //                 $transferItem['action'] = 'delete';
    //             } else if ($transferItem->form->cancellation_status === null and $transferItem->form->approval_status === 0) {
    //                 $userActivity = UserActivity::where('number', $transferItem->form->number);
    //                 $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
    //                 $updateCount = $userActivity->count();
    //                 if ($updateCount > 0) {
    //                     $transferItem['action'] = 'update';
    //                 } else {
    //                     $transferItem['action'] = 'create';
    //                 }
    //             }
    //             $no++;
    //             $ids .= $transferItem->id . ',';
    //         }
    //         $ids = substr($ids, 0, -1);
    //         $approver = User::findOrFail($data[0]->form->request_approval_to);

    //         // create token based on request_approval_to
    //         $token = Token::where('user_id', $approver->id)->first();

    //         if (!$token) {
    //             $token = new Token([
    //                 'user_id' => $approver->id,
    //                 'token' => md5($approver->email.''.now()),
    //             ]);
    //             $token->save();
    //         }

    //         if (count($data) > 1) {
    //             $form['number'] = $data[0]->form->number . ' - ' . end($data)->form->number;
    //             $form['date'] = date('d F Y', strtotime($data[0]->form->date)) . ' - ' . date('d F Y', strtotime(end($data)->form->date));
    //             $form['created'] = date('d F Y H:i:s', strtotime($data[0]->form->created_at)) . ' - ' . date('d F Y H:i:s', strtotime(end($data)->form->created_at));
    //         } else {
    //             $form['number'] = $data[0]->form->number;
    //             $form['date'] = $data[0]->form->date;
    //             $form['created'] = $data[0]->form->created_at;
    //         };

    //         DB::connection('tenant')->commit();

    //         Mail::to([
    //             $approver->email,
    //         ])->queue(new TransferItemApprovalRequestSent(
    //             $data,
    //             $approver,
    //             $form,
    //             $_SERVER['HTTP_REFERER'],
    //             $ids,
    //             $token->token
    //         ));
    //     }

    //     return [
    //         'input' => $request->all(),
    //     ];
    // }
}
