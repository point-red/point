<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Inventory\TransferReceiveItem\TransferReceive;

class TransferReceiveItemController extends Controller
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
            ->where('formable_type', TransferReceive::class)
            ->orderBy('forms.date', 'desc');
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
        $request->validate([
            'form.date' => 'required|date_format:Y-m-d h:i:s',
            'form.warehouse_from' => 'required|exists:tenant.warehouses,id',
            'form.warehouse_to' => 'required|exists:tenant.warehouses,id',
            'form.transfer_id' => 'required|exists:tenant.transfer_sends,id',
            'items.*.item' => 'required|exists:tenant.items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
        ]);
        // dd($request->all());
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $receive = TransferReceive::create($request->all());
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

        $receiveItem = TransferReceive::eloquentFilter($request)
            ->with('transferSend.form')
            ->with('form')
            ->with('warehouseFrom')
            ->with('warehouseTo')
            ->with('items.item')
            ->findOrFail($id);
        // $receiveItemIds = $receiveItem->items->pluck('id');

        return new ApiResource($receiveItem);
    }
}
