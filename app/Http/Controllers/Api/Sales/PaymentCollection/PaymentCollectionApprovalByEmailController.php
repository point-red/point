<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\UserActivity;
use App\Model\Token;
use App\Helpers\Journal\JournalHelper;
use Illuminate\Support\Facades\DB;

class PaymentCollectionApprovalByEmailController extends Controller
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
    
        $paymentCollections = PaymentCollection::whereIn('id', $request->ids)->get();
        
        foreach ($paymentCollections as $paymentCollection) {
            if ($paymentCollection->form->cancellation_status === 0) {
                $paymentCollection->form->cancellation_approval_by = $request->approver_id;
                $paymentCollection->form->cancellation_approval_at = now();
                $paymentCollection->form->cancellation_status = 1;
                $paymentCollection->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $paymentCollection->form->id;
                $userActivity->number = $paymentCollection->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Cancellation Approved by Email';
                $userActivity->save();

            } else if ($paymentCollection->form->approval_status === 0) {
                $isSuccess = PaymentCollection::checkAvailableReference($paymentCollection);
                $paymentCollection->form->approval_by = $request->approver_id;
                $paymentCollection->form->approval_at = now();
                if ($isSuccess) {
                    $paymentCollection->form->approval_status = 1;
                    $paymentCollection->form->save();

                    // Insert User Activity
                    $userActivity = new UserActivity;
                    $userActivity->table_type = 'forms';
                    $userActivity->table_id = $paymentCollection->form->id;
                    $userActivity->number = $paymentCollection->form->number;
                    $userActivity->date = now();
                    $userActivity->user_id = $request->approver_id;
                    $userActivity->activity = 'Approved by Email';
                    $userActivity->save();

                    foreach ($paymentCollection->details as $detail) {
                        if ($detail->referenceable_type === SalesInvoice::$morphName) {
                            if ($detail->available === $detail->amount) {
                                $salesInvoice = SalesInvoice::find($detail->referenceable_id);
                                $salesInvoice->form->done = 1;
                                $salesInvoice->form->save();
                            }
                        }
            
                        if ($detail->referenceable_type === SalesReturn::$morphName) {
                            if ($detail->available === $detail->amount) {
                                $salesReturn = SalesReturn::find($detail->referenceable_id);
                                $salesReturn->form->done = 1;
                                $salesReturn->form->save();
                            }
                        }
                    }
                } else {
                    $paymentCollection->form->approval_status = 0;
                    $paymentCollection->form->save();
                }
                
                
            }

            
            DB::connection('tenant')->commit();
        }


        return new ApiResource($paymentCollections);
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
        
        $paymentCollections = PaymentCollection::whereIn('id', $request->ids)->get();
        
        foreach ($paymentCollections as $paymentCollection) {
            if ($paymentCollection->form->cancellation_status === 0) {
                $paymentCollection->form->cancellation_approval_by = $request->approver_id;
                $paymentCollection->form->cancellation_approval_at = now();
                $paymentCollection->form->cancellation_approval_reason = $request->get('reason');
                $paymentCollection->form->cancellation_status = -1;
                $paymentCollection->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $paymentCollection->form->id;
                $userActivity->number = $paymentCollection->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Cancellation Rejected by Email';
                $userActivity->save();

            } else if ($paymentCollection->form->approval_status === 0) {
                $paymentCollection->form->approval_by = $request->approver_id;
                $paymentCollection->form->approval_at = now();
                $paymentCollection->form->approval_reason = $request->get('reason');
                $paymentCollection->form->approval_status = -1;
                $paymentCollection->form->save();

                // Insert User Activity
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $paymentCollection->form->id;
                $userActivity->number = $paymentCollection->form->number;
                $userActivity->date = now();
                $userActivity->user_id = $request->approver_id;
                $userActivity->activity = 'Rejected by Email';
                $userActivity->save();
            }

        }

        return new ApiResource($paymentCollections);
    }
}
