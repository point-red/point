<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Model\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Inventory\TransferSendItem\TransferSend;

class TransferSendItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $transfers = Form::select('forms.id', 'forms.date', 'forms.number', 'forms.approved', 'forms.canceled', 'forms.done')
            ->addSelect('formable_id as transfer_id')
            ->where('formable_type', TransferSend::class)
            ->orderBy('forms.date', 'desc');

        if(isset($request->status) AND $request->status == 'pending') {
            $transfers->where('forms.done' , '<>', 1);
        }

        $transfers = pagination($transfers, $request->input('limit'));

        return new ApiCollection($transfers);
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
            'items.*.item' => 'required|exists:tenant.items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
        ]);
        // dd($request->all());
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $transfer = TransferSend::create($request->all());
            $transfer
                ->load('form')
                ->load('items.item');

            return new ApiResource($transfer);
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
        $transferItem = TransferSend::eloquentFilter($request)
            ->with('form')
            ->with('warehouseFrom')
            ->with('warehouseTo')
            ->with('items.item')
            ->findOrFail($id);

        // $transferItemIds = $transferItem->items->pluck('id');

        return new ApiResource($transferItem);
    }
}
