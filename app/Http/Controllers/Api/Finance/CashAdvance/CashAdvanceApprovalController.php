<?php

namespace App\Http\Controllers\Api\Finance\CashAdvance;

use App\Exceptions\ApprovalNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\PointException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashAdvanceApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function approve(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);
        if(!$cashAdvance->isAllowedToApprove($cashAdvance)){
            throw new PointException('Balance Not Enough');
        }
        $cashAdvance->form->approval_by = auth()->user()->id;
        $cashAdvance->form->approval_at = now();
        $cashAdvance->form->approval_status = 1;
        $cashAdvance->form->save();

        $cashAdvance->mapHistory($cashAdvance, $request->all());

        return new ApiResource($cashAdvance);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws ApprovalNotFoundException
     * @throws UnauthorizedException
     */
    public function reject(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->form->approval_by = auth()->user()->id;
        $cashAdvance->form->approval_at = now();
        $cashAdvance->form->approval_reason = $request->get('reason');
        $cashAdvance->form->approval_status = -1;
        $cashAdvance->form->save();

        $cashAdvance->mapHistory($cashAdvance, $request->all());

        return new ApiResource($cashAdvance);
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
            //verify token
            $token = Token::where('token', $request->get('token'))->first();
            if(!$token){
                throw new PointException('Not Authorized');
            }
            
            $cashAdvance = CashAdvance::with('form')->findOrFail($request->get('id'));
            if($cashAdvance->form->approval_status == 0){
                if($request->get('status') == -1 || ($cashAdvance->isAllowedToApprove($cashAdvance) && $request->get('status') == 1)){
                    $cashAdvance->form->approval_by = $token->user_id;
                    $cashAdvance->form->approval_at = now();
                    $cashAdvance->form->approval_status = $request->get('status');
                    if($request->get('status') == -1){
                        $cashAdvance->form->approval_reason = 'rejected by email';
                    }
                    $cashAdvance->form->save();
    
                    $data['activity'] = $request->get('activity');
                    $data['user_id'] = $token->user_id;
    
                    $cashAdvance->mapHistory($cashAdvance, $data);
                }
            }

            return new ApiResource($cashAdvance);
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
            //verify token
            $token = Token::where('token', $request->get('token'))->first();
            if(!$token){
                throw new PointException('Not Authorized');
            }

            $bulkId = $request->get('bulk_id');
            foreach($bulkId as $id){
                $cashAdvance = CashAdvance::findOrFail($id);
                if($cashAdvance->form->approval_status == 0){
                    if($request->get('status') == -1 || ($cashAdvance->isAllowedToApprove($cashAdvance) && $request->get('status') == 1)){
                        $cashAdvance->form->approval_by = $token->user_id;
                        $cashAdvance->form->approval_at = now();
                        $cashAdvance->form->approval_status = $request->get('status');
                        if($request->get('status') == -1){
                            $cashAdvance->form->approval_reason = 'rejected by email';
                        }
                        $cashAdvance->form->save();

                        $data['activity'] = $request->get('activity');
                        $data['user_id'] = $token->user_id;

                        $cashAdvance->mapHistory($cashAdvance, $data);
                    }
                }
            }

            $cashAdvances = CashAdvance::whereIn('id', $bulkId)->with('form')->get();

            return new ApiResource($cashAdvances);
        });
    }
}
