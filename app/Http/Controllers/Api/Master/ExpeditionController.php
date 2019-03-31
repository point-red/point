<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Bank;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use Illuminate\Http\Request;
use App\Model\Master\Address;
use App\Model\Master\Expedition;
use App\Http\Resources\ApiResource;
use App\Model\Master\ContactPerson;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Requests\Master\Expedition\StoreExpeditionRequest;

class ExpeditionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $expeditions = Expedition::eloquentFilter($request);
        $expeditions = pagination($expeditions, $request->get('limit'));

        return new ApiCollection($expeditions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreExpeditionRequest  $request
     * @return ApiResource
     */
    public function store(StoreExpeditionRequest $request)
    {
        \DB::connection('tenant')->beginTransaction();

        $expedition = new Expedition;
        $expedition->fill($request->all());
        $expedition->save();

        Address::saveFromRelation($expedition, $request->get('addresses'));
        Phone::saveFromRelation($expedition, $request->get('phones'));
        Email::saveFromRelation($expedition, $request->get('emails'));
        ContactPerson::saveFromRelation($expedition, $request->get('contacts'));
        Bank::saveFromRelation($expedition, $request->get('banks'));

        \DB::connection('tenant')->commit();

        return new ApiResource($expedition);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  Request  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $expedition = Expedition::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($expedition);
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
    public function destroy($id)
    {
        $expedition = Expedition::findOrFail($id);
        $expedition->delete();

        return response()->json([], 204);
    }
}
