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
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Master\Person\PersonCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        $persons = Person::join('person_categories', 'person_categories.id', '=', 'persons.person_category_id')
            ->join('person_groups', 'person_groups.id', '=', 'persons.person_group_id')
            ->select('persons.*', 'person_categories.name as category_name', 'person_groups.name as group_name');

        if ($request->input('category')) {
            $persons = $persons->where('person_categories.name', $request->input('category'));
        }

        $persons = $persons->paginate($limit);

        return new PersonCollection($persons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Master\Person\PersonResource
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
        $person->person_category_id = $request->input('person_category_id');
        $person->person_group_id = $request->input('person_group_id');
        $person->save();

        return new PersonResource($person);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\Person\PersonResource
     */
    public function show($id)
    {
        $person = Person::join('person_categories', 'person_categories.id', '=', 'persons.person_category_id')
            ->join('person_groups', 'person_groups.id', '=', 'persons.person_group_id')
            ->where('persons.id', $id)
            ->select('persons.*', 'person_categories.name as category_name', 'person_groups.name as group_name')
            ->first();

        return new PersonResource($person);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\Person\PersonResource
     */
    public function update(UpdatePersonRequest $request, $id)
    {
        $person = Person::findOrFail($id);
        $person->code = $request->input('code');
        $person->name = $request->input('name');
        $person->phone = $request->input('phone');
        $person->address = $request->input('address');
        $person->notes = $request->input('notes');
        $person->person_category_id = $request->input('person_category_id');
        $person->person_group_id = $request->input('person_group_id');
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
        Person::findOrFail($id)->delete();

        return response(null, 204);
    }
}
