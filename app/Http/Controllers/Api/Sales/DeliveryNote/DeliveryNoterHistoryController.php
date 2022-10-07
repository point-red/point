<?php

namespace App\Http\Controllers\Api\Sales\deliveryNote;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use App\Model\UserActivity;
use Illuminate\Http\Request;

class DeliveryNoterHistoryController extends Controller
{
    /**
     * @param  Request  $request
     * @return ApiCollection
     */
    public function index(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        $formNumber = $deliveryNote->form->number;

        $histories = UserActivity::select(UserActivity::getTableName().'.*', Form::$alias.'.formable_id')
            ->eloquentFilter($request)
            ->join(Form::getTableName().' as '.Form::$alias, function ($query) {
                $query->on(Form::$alias.'.formable_id', '=', UserActivity::getTableName().'.table_id');
                $query->on(Form::$alias.'.formable_type', '=', UserActivity::getTableName().'.table_type');
            })
            ->where(UserActivity::getTableName().'.number', $formNumber);

        $histories = pagination($histories, $request->limit);

        return new ApiCollection($histories);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        if ($request->id) {
            $deliveryNote = DeliveryNote::findOrFail($request->id);

            $activity = $request->activity;
            $number = $deliveryNote->form->number;

            $userActivity = new UserActivity;
            $userActivity->table_type = $deliveryNote::$morphName;
            $userActivity->table_id = $deliveryNote->id;
            $userActivity->number = $number;
            $userActivity->date = now();
            $userActivity->user_id = auth()->user()->id;
            $userActivity->activity = $activity;
            $userActivity->save();
        }

        return new ApiResource($userActivity);
    }
}
