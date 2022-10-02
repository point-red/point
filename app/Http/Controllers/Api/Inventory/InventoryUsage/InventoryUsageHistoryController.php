<?php

namespace App\Http\Controllers\Api\Inventory\InventoryUsage;

use Illuminate\Http\Request;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Form;
use App\Model\UserActivity;
use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request, $id)
    {
        $inventoryUsage = InventoryUsage::findOrFail($id);
        $formNumber = $inventoryUsage->form->number;
        
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    // public function store(Request $request)
    // {
    //     if ($request->id) {
    //         $inventoryUsage = InventoryUsage::findOrFail($request->id);

    //         $activity = $request->activity;
    //         $number = $inventoryUsage->form->number;

    //         // Insert User Activity
    //         $userActivity = new UserActivity;
    //         $userActivity->table_type = $inventoryUsage::$morphName;
    //         $userActivity->table_id = $inventoryUsage->id;
    //         $userActivity->number = $number;
    //         $userActivity->date = now();
    //         $userActivity->user_id = auth()->user()->id;
    //         $userActivity->activity = $activity;
    //         $userActivity->save();
    //     };

    //     return new ApiResource($userActivity);
    // }
}
