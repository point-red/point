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
        
        $histories = UserActivity::select(UserActivity::getTableName().'.*', Form::$alias.'.formable_id')
            ->eloquentFilter($request)
            ->join(Form::getTableName().' as '.Form::$alias, function ($query) {
                $query->on(Form::$alias.'.id', '=', UserActivity::getTableName().'.table_id');
            })
            ->where(UserActivity::getTableName().'.number', $formNumber);

        $histories = pagination($histories, $request->limit);

        return new ApiCollection($histories);
    }
}
