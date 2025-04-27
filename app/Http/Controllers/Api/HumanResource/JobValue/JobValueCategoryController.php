<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\JobValue\JobValueCategory\JobValueCategoryCollection;
use App\Http\Requests\HumanResource\JobValue\JobValueCategory\StoreJobValueCategoryRequest;
use App\Model\HumanResource\JobValue\JobValueCategory;
use Illuminate\Http\Request;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;

class JobValueCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new JobValueCategoryCollection(JobValueCategory::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\HumanResource\JobValue\JobValueCategory\StoreJobValueCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJobValueCategoryRequest $request)
    {
        $category = new JobValueCategory;
        $category->fill($request->all());
        $category->save();

        return new ApiResource($category);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
