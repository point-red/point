<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\Papikostick;
use App\Model\Psychotest\PapikostickCategory;
use App\Model\Psychotest\PapikostickResult;

use App\Http\Resources\Psychotest\Papikostick\PapikostickResource;
use App\Http\Resources\Psychotest\Papikostick\PapikostickCollection;

use App\Http\Requests\Psychotest\Papikostick\StorePapikostickRequest;
use App\Http\Requests\Psychotest\Papikostick\UpdatePapikostickRequest;

class PapikostickController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\Papikostick\PapikostickCollection
     */
    public function index(Request $request)
    {
        $papikosticks = Papikostick::eloquentFilter($request)->select('psychotest_papikosticks.*');
        $papikosticks = pagination($papikosticks, $request->input('limit'));

        return new PapikostickCollection($papikosticks);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Psychotest\Papikostick\StorePapikostickRequest  $request
     * @return \App\Http\Resources\Psychotest\Papikostick\PapikostickResource
     */
    public function store(StorePapikostickRequest $request)
    {
        
        $validated = $request->validated();

        if ($validated) {
            $papikostick = new Papikostick();
            $papikostick->candidate_id = $validated['candidate_id'];
            
            $papikostick->save();
            
            $papikostickCategories = PapikostickCategory::all();
            foreach ($papikostickCategories as $category) {
                $papikostickResult = new PapikostickResult();

                $papikostickResult->category_id = $category->id;
                $papikostickResult->papikostick_id = $papikostick->id;

                $papikostickResult->save();
            }

            return new PapikostickResource($papikostick);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Papikostick\PapikostickResource
     */
    public function show($id)
    {
        $papikostick = Papikostick::findOrFail($id);

        return new PapikostickResource($papikostick);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Psychotest\Papikostick\UpdatePapikostickRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Papikostick\PapikostickResource
     */
    public function update(UpdatePapikostickRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick = Papikostick::findOrFail($id);
            $papikostick->candidate_id = $validated['candidate_id'];

            $papikostick->save();

            return new PapikostickResource($papikostick);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Papikostick\PapikostickResource
     */
    public function destroy($id)
    {
        $papikostick = Papikostick::findOrFail($id);
        $papikostick->delete();

        return new PapikostickResource($papikostick);
    }
}
