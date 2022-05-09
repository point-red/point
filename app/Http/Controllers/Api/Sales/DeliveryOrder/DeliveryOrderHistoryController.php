<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\UserActivity;
use App\Model\Form;
use App\Http\Resources\ApiCollection;
use Illuminate\Http\Request;

class DeliveryOrderHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $formNumber = $deliveryOrder->form->number;
        
        $histories = UserActivity::from(UserActivity::getTableName().' as '.UserActivity::$alias)
            ->eloquentFilter($request)
            ->join(Form::getTableName().' as '.Form::$alias, function ($query) {
                $query->on(Form::$alias.'.id', '=', UserActivity::$alias.'.table_id');
            })
            ->where(UserActivity::$alias.'.number', $formNumber)
            ->select(UserActivity::$alias.'.*', Form::$alias.'.formable_id');

        $histories = pagination($histories, $request->limit);

        return new ApiCollection($histories);
    }
}
