<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseOrder;

use App\Exceptions\PointException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Mail\PurchaseOrderBulkRequestApprovalNotificationMail;
use App\Model\Master\User;
use App\Model\Project\Project;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->form->approval_by = auth()->user()->id;
        $purchaseOrder->form->approval_at = now();
        $purchaseOrder->form->approval_status = 1;
        $purchaseOrder->form->save();

        $purchaseOrder->form->fireEventApproved();

        return new ApiResource($purchaseOrder);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->form->approval_by = auth()->user()->id;
        $purchaseOrder->form->approval_at = now();
        $purchaseOrder->form->approval_reason = $request->get('reason');
        $purchaseOrder->form->approval_status = -1;
        $purchaseOrder->form->save();

        $purchaseOrder->form->fireEventRejected();

        return new ApiResource($purchaseOrder);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendBulkRequestApproval(Request $request)
    {
        $purchaseOrderGroup = PurchaseOrder::whereIn('id', $request->get('bulk_id'))
                       ->with('form.requestApprovalTo','form.createdBy', 'supplier', 'items')
                       ->get()
                       ->groupBy('form.requestApprovalTo.email');

        foreach($purchaseOrderGroup as $email => $purchaseOrders){
            // create token based on request_approval_to
            $approver = User::findOrFail($purchaseOrders[0]->form->request_approval_to);
            $token = Token::where('user_id', $approver->id)->first();

            if (!$token) {
                $token = new Token([
                    'user_id' => $approver->id,
                    'token' => md5($approver->email.''.now()),
                ]);
                $token->save();
            }

            $project = Project::where('code', $request->header('Tenant'))->first();

            Mail::to($email)->send(new PurchaseOrderBulkRequestApprovalNotificationMail($purchaseOrders, $request->header('Tenant'), $request->get('tenant_url'), $request->get('bulk_id'), $token->token, $project->name));

            // record history
            foreach($purchaseOrders as $purchaseOrder){
                $purchaseOrder->form->fireEventRequestApproval();
            }
        }

        return response()->json([], 204);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Json
     * @throws PointException
     */
    public function approvalWithToken(Request $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            // verify token
            $token = Token::where('token', $request->get('token'))->first();
            if(!$token){
                throw new PointException('Not Authorized');
            }

            $purchaseOrder = PurchaseOrder::with('form')->findOrFail($request->get('id'));
            if($purchaseOrder->form->approval_status == 0){
                if($request->get('status') == -1 || $request->get('status') == 1) {
                    $purchaseOrder->form->approval_by = $token->user_id;
                    $purchaseOrder->form->approval_at = now();
                    $purchaseOrder->form->approval_status = $request->get('status');
                    if($request->get('status') == -1) {
                        $purchaseOrder->form->approval_reason = 'rejected by email';
                    } else if($request->get('status') == 1) {
                        $purchaseOrder->form->approval_reason = 'approved by email';
                    }
                    $purchaseOrder->form->save();

                    // record history
                    if($request->get('status') == -1) {
                        $purchaseOrder->form->fireEventRejectedByEmail();
                    } else if($request->get('status') == 1) {
                        $purchaseOrder->form->fireEventApprovedByEmail();
                    }
                }
            }

            return new ApiResource($purchaseOrder);
        });
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function bulkApprovalWithToken(Request $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            // verify token
            $token = Token::where('token', $request->get('token'))->first();
            if(!$token){
                throw new PointException('Not Authorized');
            }

            $bulkId = $request->get('bulk_id');
            $purchaseOrders = PurchaseOrder::with('form')->whereIn('id', $bulkId)->get();
            foreach($purchaseOrders as $purchaseOrder) {
                if($purchaseOrder->form->approval_status == 0){
                    if($request->get('status') == -1 || $request->get('status') == 1) {
                        $purchaseOrder->form->approval_by = $token->user_id;
                        $purchaseOrder->form->approval_at = now();
                        $purchaseOrder->form->approval_status = $request->get('status');
                        if($request->get('status') == -1) {
                            $purchaseOrder->form->approval_reason = 'rejected by email';
                        } else if($request->get('status') == 1) {
                            $purchaseOrder->form->approval_reason = 'approved by email';
                        }
                        $purchaseOrder->form->save();

                        // record history
                        if($request->get('status') == -1) {
                            $purchaseOrder->form->fireEventRejectedByEmail();
                        } else if($request->get('status') == 1) {
                            $purchaseOrder->form->fireEventApprovedByEmail();
                        }
                    }
                }
            }

            return new ApiResource($purchaseOrders);
        });
    }
}
