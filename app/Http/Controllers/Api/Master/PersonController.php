<?php

namespace App\Http\Controllers\Api\Master;

use App\Model\Master\Person;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Master\Person\PersonResource;
use App\Http\Resources\Master\Person\PersonCollection;
use App\Http\Requests\Master\Person\StorePersonRequest;
use App\Http\Requests\Master\Person\UpdatePersonRequest;

class PersonController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new PersonCollection(Person::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePersonRequest $request)
    {
        $person = new Person;
        $person->code = $request->input('code');
        $person->name = $request->input('name');
        $person->email = $request->input('email');
        $person->phone = $request->input('phone');
        $person->address = $request->input('address');
        $person->notes = $request->input('notes');
        $person->person_categories_id = $request->input('person_categories_id');
        $person->person_groups_id = $request->input('person_groups_id');
        $person->save();

        return new PersonResource($person);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new PersonResource(Person::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePersonRequest $request, $id)
    {
        $person = Person::find($id);
        $person->code = $request->input('code');
        $person->name = $request->input('name');
        $person->phone = $request->input('phone');
        $person->address = $request->input('address');
        $person->notes = $request->input('notes');
        $person->person_categories_id = $request->input('person_categories_id');
        $person->person_groups_id = $request->input('person_groups_id');
        $person->save();

        return new PersonResource($person);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Person::find($id)->delete();

        return response(null, 204);
    }
}
