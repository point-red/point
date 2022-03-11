<?php

namespace App\Http\Controllers\Api\Finance\CashAdvance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CashAdvance\StoreCashAdvanceRequest;
use App\Http\Requests\Finance\CashAdvance\UpdateCashAdvanceRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Mail\CashAdvanceBulkRequestApprovalNotificationMail;
use App\Model\Finance\CashAdvance\CashAdvance;
use App\Model\UserActivity;
use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $cashAdvance = CashAdvance::from(CashAdvance::getTableName().' as '.CashAdvance::$alias)->eloquentFilter($request);

        $cashAdvance = CashAdvance::joins($cashAdvance, $request->get('join'));

        $cashAdvance = pagination($cashAdvance, $request->get('limit'));

        return new ApiCollection($cashAdvance);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function history(Request $request)
    {
        $userActivity = UserActivity::from(UserActivity::getTableName().' as '.UserActivity::$alias)->eloquentFilter($request);

        $userActivity = pagination($userActivity, $request->get('limit'));

        return new ApiCollection($userActivity);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCashAdvanceRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreCashAdvanceRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $cashAdvance = CashAdvance::create($request->all());
            $cashAdvance->mapHistory($cashAdvance, $request->all());
            $cashAdvance
                ->load('form')
                ->load('details.account')
                ->load('employee');

            return new ApiResource($cashAdvance);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $cashAdvance = CashAdvance::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($cashAdvance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCashAdvanceRequest $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function update(UpdateCashAdvanceRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $cashAdvance = CashAdvance::findOrFail($id);
            $cashAdvance->isAllowedToUpdate();
            $cashAdvance->mapHistory($cashAdvance, $request->all());
            $cashAdvance->archive();

            $cashAdvanceNew = CashAdvance::create($request->all());
            $cashAdvanceNew->created_at = date("Y-m-d H:i:s", strtotime($cashAdvance->created_at.' Asia/Jakarta'));
            $cashAdvanceNew->save();

            $cashAdvanceNew->form->increment = $cashAdvance->form->increment;
            $cashAdvanceNew->form->save();

            $cashAdvanceNew
                ->load('form')
                ->load('details.account')
                ->load('employee');

            return new ApiResource($cashAdvanceNew);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->isAllowedToDelete();

        $response = $cashAdvance->requestCancel($request);

        $cashAdvance->mapHistory($cashAdvance, $request->all());

        return response()->json([], 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return Response
     */
    public function sendBulkRequestApproval(Request $request)
    {
        $cashAdvanceGroup = CashAdvance::whereIn('id', $request->get('bulk_id'))
                       ->with('form.requestApprovalTo','form.createdBy','details.account', 'employee')
                       ->get()
                       ->groupBy('form.requestApprovalTo.email');
        
        foreach($cashAdvanceGroup as $email => $cashAdvances){
            Mail::to($email)->send(new CashAdvanceBulkRequestApprovalNotificationMail($cashAdvances, $request->get('tenant_base_url'), $request->get('bulk_id')));
            //set timestamp
            foreach($cashAdvances as $cashAdvance){
                $cashAdvance->timestampRequestApproval();
                $cashAdvance->mapHistory($cashAdvance, $request->all());
            }
        }

        return response()->json([], 204);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws UnauthorizedException
     * @throws ApprovalNotFoundException
     */
    public function refund(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);
        $cashAdvance->isAllowedToRefund();
        $cashAdvance->amount_remaining = 0;
        $cashAdvance->save();

        $cashAdvance->form->done = 1;
        $cashAdvance->form->save();

        $cashAdvance->mapHistory($cashAdvance, $request->all());

        return new ApiResource($cashAdvance);
    }
}
