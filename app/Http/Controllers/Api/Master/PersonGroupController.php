<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Model\Master\PersonGroup;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Master\PersonGroup\PersonGroupResource;
use App\Http\Resources\Master\PersonGroup\PersonGroupCollection;
use App\Http\Requests\Master\PersonGroup\StorePersonGroupRequest;
use App\Http\Requests\Master\PersonGroup\UpdatePersonGroupRequest;

class PersonGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new PersonGroupCollection(PersonGroup::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePersonGroupRequest $request)
    {
        $personGroup = new PersonGroup;
        $personGroup->code = $request->input('code');
        $personGroup->name = $request->input('name');
        $personGroup->save();

        return new PersonGroupResource($personGroup);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new PersonGroupResource(PersonGroup::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePersonGroupRequest $request, $id)
    {
        $personGroup = PersonGroup::findOrFail($id);
        $personGroup->code = $request->input('code');
        $personGroup->name = $request->input('name');
        $personGroup->save();

        return new PersonGroupResource($personGroup);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        PersonGroup::findOrFail($id)->delete();

        return response(null, 204);
    }
}
