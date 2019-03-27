<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Inventory\Receive\Receive;

class ReceiveItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $receives = Form::select('forms.id', 'forms.date', 'forms.number', 'forms.approved', 'forms.canceled', 'forms.done')
            ->addSelect('formable_id as receive_id')
            ->where('formable_type', Receive::class);
        // dd($receives->toSql());

        $receives = pagination($receives, $request->input('limit'));

        return new ApiCollection($receives);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Illuminate\Http\Request $request
     * @return \App\Http\Resources\ApiResource
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $receive = Receive::create($request->all());
            $receive
                ->load('form')
                ->load('items.item');

            return new ApiResource($receive);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function show(Request $request, $id)
    {
        $receiveItem = Receive::eloquentFilter($request)
            ->with('form')
            ->with('warehouseFrom')
            ->with('warehouseTo')
            ->with('items.item')
            ->findOrFail($id);

        // $receiveItemIds = $receiveItem->items->pluck('id');

        return new ApiResource($receiveItem);
    }

}
