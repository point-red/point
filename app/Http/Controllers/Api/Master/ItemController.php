<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\Item\StoreItemRequest;
use App\Http\Requests\Master\Item\UpdateItemRequest;
use App\Http\Controllers\Controller;
use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Item::eloquentFilter(request())
            ->with('groups')
            ->with('units')
            // ->with('contact_people')
            ->paginate(request()->get('paginate') ?? 20);

        return new ApiCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Customer\StoreCustomerRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(Request $request)
    {
        DB::connection('tenant')->beginTransaction();

        $item = new Item;
        $item->fill($request->all());
        $item->save();

        $units = $request->get('units');
        $unitsToBeInserted = [];
        if ($units) {
            foreach($units as $unit) {
                $itemUnit = new ItemUnit();
                $itemUnit->fill($unit);
                array_push($unitsToBeInserted, $itemUnit);
            }
        }
        $item->units()->saveMany($unitsToBeInserted);
        
        DB::connection('tenant')->commit();
        
        return new ApiResource($item);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Item::eloquentFilter(request())
            ->with('units')
            ->findOrFail($id);
        return new ApiResource($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Master\Item\UpdateItemRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateItemRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $item = Item::findOrFail($id);
        $item->fill($request->all());
        $item->save();

        $units = $request->get('units');
        $unitsToBeInserted = [];
        if ($units) {
            foreach($units as $unit) {
                $itemUnit = new ItemUnit();
                $itemUnit->fill($unit);
                array_push($unitsToBeInserted, $itemUnit);
            }
        }
        $item->units()->saveMany($unitsToBeInserted);
        
        DB::connection('tenant')->commit();
        
        return new ApiResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([], 204);
    }
}
