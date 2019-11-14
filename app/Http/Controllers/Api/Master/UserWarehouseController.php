<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserWarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::findOrFail($request->get('id'));

        if (! $user->warehouses->contains($request->get('warehouse_id'))) {
            $user->warehouses()->attach($request->get('warehouse_id'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'data' => $user->warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->warehouses->contains($request->get('warehouse_id'))) {
            $user->warehouses()->detach($request->get('warehouse_id'));
        }

        return response()->json([], 204);
    }
}
