<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\PersonCategory\StorePersonCategoryRequest;
use App\Http\Requests\Master\PersonCategory\UpdatePersonCategoryRequest;
use App\Http\Resources\Master\PersonCategory\PersonCategoryCollection;
use App\Http\Resources\Master\PersonCategory\PersonCategoryResource;
use App\Model\Master\PersonCategory;
use Illuminate\Http\Request;

class PersonCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new PersonCategoryCollection(PersonCategory::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePersonCategoryRequest $request)
    {
        $personCategory = new PersonCategory;
        $personCategory->code = $request->input('code');
        $personCategory->name = $request->input('name');
        $personCategory->save();

        return new PersonCategoryResource($personCategory);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new PersonCategoryResource(PersonCategory::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePersonCategoryRequest $request, $id)
    {
        $personCategory = PersonCategory::find($id);
        $personCategory->code = $request->input('code');
        $personCategory->name = $request->input('name');
        $personCategory->save();

        return new PersonCategoryResource($personCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        PersonCategory::find($id)->delete();

        return response(null, 204);
    }

}
