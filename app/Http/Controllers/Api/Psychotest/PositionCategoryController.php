<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\CandidatePosition;
use App\Model\Psychotest\PapikostickCategory;

use App\Model\Psychotest\PositionCategory;

use App\Http\Resources\Psychotest\PositionCategory\PositionCategoryResource;
use App\Http\Resources\Psychotest\PositionCategory\PositionCategoryCollection;

use App\Http\Requests\Psychotest\PositionCategory\StorePositionCategoryRequest;
use App\Http\Requests\Psychotest\PositionCategory\UpdatePositionCategoryRequest;

class PositionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryCollection
     */
    public function index(Request $request)
    {
        $positionCategories = PositionCategory::eloquentFilter($request)->select('psychotest_position_categories.*');
        $positionCategories = pagination($positionCategories, $request->input('limit'));

        return new PositionCategoryCollection($positionCategories);
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
     * Bulk store a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryCollection
     */
    public function bulk_store(Request $request)
    {
        $position = CandidatePosition::findOrFail($request->input('position_id'));
        $categories = $request->input('categories');

        foreach ($categories as $cat) {
            $category = PapikostickCategory::findOrFail($cat['category_id']);

            $positionCategory = new PositionCategory();

            $positionCategory->position_id = $position->id;
            $positionCategory->category_id = $category->id;
            $positionCategory->category_max = $cat['category_max'];
            $positionCategory->category_min = $cat['category_min'];

            $positionCategory->save();
        }

        $positionCategories = PositionCategory::eloquentFilter($request)->select('psychotest_position_categories.*')->where('position_id', '=', $position->id);
        $positionCategories = pagination($positionCategories, $request->input('limit'));

        return new PositionCategoryCollection($positionCategories);
    }

    /**
     * Bulk update a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryCollection
     */
    public function bulk_update(Request $request)
    {
        $position = CandidatePosition::findOrFail($request->input('position_id'));
        $categories = $request->input('categories');

        foreach ($categories as $cat) {
            $category = PapikostickCategory::findOrFail($cat['category_id']);

            $positionCategory = PositionCategory::findOrFail($cat['id']);

            $positionCategory->position_id = $position->id;
            $positionCategory->category_id = $category->id;
            $positionCategory->category_max = $cat['category_max'];
            $positionCategory->category_min = $cat['category_min'];

            $positionCategory->save();
        }

        $positionCategories = PositionCategory::eloquentFilter($request)->select('psychotest_position_categories.*')->where('position_id', '=', $position->id);
        $positionCategories = pagination($positionCategories, $request->input('limit'));

        return new PositionCategoryCollection($positionCategories);
    }

    /**
     * Bulk delete resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryCollection
     */
    public function bulk_delete(Request $request)
    {
        $position = CandidatePosition::findOrFail($request->input('position_id'));
        $categories = $request->input('categories');

        foreach ($categories as $cat) {
            $positionCategory = PositionCategory::findOrFail($cat['id']);

            $positionCategory->delete();
        }

        return response()->json(['ok' => 'ok']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Psychotest\PositionCategory\StorePositionCategoryRequest  $request
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryResource
     */
    public function store(StorePositionCategoryRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $positionCategory = new PositionCategory();

            $position = CandidatePosition::findOrFail($validated["position_id"]);
            $category = PapikostickCategory::findOrFail($validated["category_id"]);

            $positionCategory->category_max = $validated["category_max"];
            $positionCategory->category_min = $validated["category_min"];
            $positionCategory->position_id = $position->id;
            $positionCategory->category_id = $category->id;

            $positionCategory->save();

            return new PositionCategoryResource($positionCategory);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
      * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryResource
     */
    public function show($id)
    {
        $positionCategory = PositionCategory::findOrFail($id);

        return new PositionCategoryResource($positionCategory);
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
     * @param  \App\Http\Requests\Psychotest\PositionCategory\UpdatePositionCategoryRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryResource
     */
    public function update(UpdatePositionCategoryRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $positionCategory = PositionCategory::findOrFail($id);

            $position = CandidatePosition::findOrFail($validated["position_id"]);
            $category = PapikostickCategory::findOrFail($validated["category_id"]);

            $positionCategory->category_max = $validated["category_max"];
            $positionCategory->category_min = $validated["category_min"];
            $positionCategory->position_id = $position->id;
            $positionCategory->category_id = $category->id;

            $positionCategory->save();

            return new PositionCategoryResource($positionCategory);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PositionCategory\PositionCategoryResource
     */
    public function destroy($id)
    {
        $positionCategory = PositionCategory::findOrFail($id);
        $positionCategory->delete();

        return new PositionCategoryResource($positionCategory);
    }
}
