<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\UserActivity;
use App\Model\Form;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use Illuminate\Http\Request;

class PaymentCollectionHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request, $id)
    {
        $paymentCollection = PaymentCollection::findOrFail($id);
        $form_number = $paymentCollection->form->number;
        
        $histories = UserActivity::from(UserActivity::getTableName().' as '.UserActivity::$alias)->eloquentFilter($request);
        
        $histories = $histories->where(UserActivity::$alias.'.number', $form_number);

        $histories = $histories->join(Form::getTableName().' as '.Form::$alias, function ($q) {
            $q->on(Form::$alias.'.id', '=', UserActivity::$alias.'.table_id');
        });

        $histories = $histories->select(UserActivity::$alias.'.*', Form::$alias.'.formable_id');

        $histories = pagination($histories, $request->limit ?: 10);

        return new ApiCollection($histories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        if ($request->id) {
            $paymentCollection = PaymentCollection::findOrFail($request->id);

            if ($request->activity == 'Update') {
                $userActivity = UserActivity::where('number', $paymentCollection->form->edited_number);
                $userActivity = $userActivity->where('activity', 'like', '%' . 'Update' . '%');
                $updateNumber = $userActivity->count() + 1;
                $activity = $request->activity . ' - ' . $updateNumber;
                $number = $paymentCollection->form->edited_number;
            } else {
                $activity = $request->activity;
                $number = $paymentCollection->form->number;
            }

            // Insert User Activity
            $userActivity = new UserActivity;
            $userActivity->table_type = 'forms';
            $userActivity->table_id = $paymentCollection->form->id;
            $userActivity->number = $number;
            $userActivity->date = now();
            $userActivity->user_id = auth()->user()->id;
            $userActivity->activity = $activity;
            $userActivity->save();
        };

        if ($request->ids) {
            $paymentCollections = PaymentCollection::whereIn('id', $request->ids)->get();

            // Insert User Activity
            foreach ($paymentCollections as $paymentCollection) {
                $userActivity = new UserActivity;
                $userActivity->table_type = 'forms';
                $userActivity->table_id = $paymentCollection->form->id;
                $userActivity->number = $request->activity == 'Update' ? $paymentCollection->form->edited_number : $paymentCollection->form->number;
                $userActivity->date = now();
                $userActivity->user_id = auth()->user()->id;
                $userActivity->activity = $request->activity;
                $userActivity->save();
            }
        };

        return new ApiResource($userActivity);
    }
}
