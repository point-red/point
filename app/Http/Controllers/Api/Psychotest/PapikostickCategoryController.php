<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\PapikostickCategory;

use App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryCollection;
use App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryResource;

use App\Http\Requests\Psychotest\PapikostickCategory\StorePapikostickCategoryRequest;
use App\Http\Requests\Psychotest\PapikostickCategory\UpdatePapikostickCategoryRequest;

class PapikostickCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryCollection
     */
    public function index(Request $request)
    {
        $papikostick_categories = PapikostickCategory::eloquentFilter($request)->select('psychotest_papikostick_categories.*');
        $papikostick_categories = pagination($papikostick_categories, $request->input('limit'));
        
        return new PapikostickCategoryCollection($papikostick_categories);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickCategory\StorePapikostickCategoryRequest  $request
     * @return \App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryResource
     */
    public function store(StorePapikostickCategoryRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_category = new PapikostickCategory();
            $papikostick_category->name = $validated['name'];
            $papikostick_category->description = $validated['description'];

            $papikostick_category->save();
            
            return new PapikostickCategoryResource($papikostick_category);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryResource
     */
    public function show($id)
    {
        $papikostick_category = PapikostickCategory::findOrFail($id);

        return new PapikostickCategoryResource($papikostick_category);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickCategory\UpdatePapikostickCategoryRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryResource
     */
    public function update(UpdatePapikostickCategoryRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_category = PapikostickCategory::findOrFail($id);
            $papikostick_category->name = $validated['name'];
            $papikostick_category->description = $validated['description'];

            $papikostick_category->save();

            return new PapikostickCategoryResource($papikostick_category);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickCategory\PapikostickCategoryResource
     */
    public function destroy($id)
    {
        $papikostick_category = PapikostickCategory::findOrFail($id);
        $papikostick_category->delete();

        return new PapikostickCategoryResource($papikostick_category);
    }
}
