<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Model\Master\User;
use Illuminate\Http\Request;
use App\Model\Form;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\ApiResource;
use App\Model\Inventory\Transfer\Transfer;
use App\Http\Requests\Inventory\Transfer\TransferItemRequest;

class TransferItemController extends Controller
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
            ->where('formable_type', Transfer::class);
        // dd($transfers->toSql());

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
        // dd($request->all());
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $transfer = Transfer::create($request->all());
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
        $transferItem = Transfer::eloquentFilter($request)
            ->with('form')
            ->with('warehouseFrom')
            ->with('warehouseTo')
            ->with('items.item')
            ->findOrFail($id);
        
        // $transferItemIds = $transferItem->items->pluck('id');

        return new ApiResource($transferItem);
    }

}
