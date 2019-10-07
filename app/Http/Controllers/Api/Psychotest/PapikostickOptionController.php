<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\PapikostickOption;
use App\Model\Psychotest\PapikostickCategory;
use App\Model\Psychotest\PapikostickQuestion;

use App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionCollection;
use App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionResource;

use App\Http\Requests\Psychotest\PapikostickOption\StorePapikostickOptionRequest;
use App\Http\Requests\Psychotest\PapikostickOption\UpdatePapikostickOptionRequest;

class PapikostickOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionCollection
     */
    public function index(Request $request)
    {
        $papikostick_options = PapikostickOption::eloquentFilter($request)->select('psychotest_papikostick_options.*');
        $papikostick_options = pagination($papikostick_options, $request->input('limit'));

        return new PapikostickOptionCollection($papikostick_options);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickOption\StorePapikostickOptionRequest  $request
     * @return \App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionResource
     */
    public function store(StorePapikostickOptionRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $question = PapikostickQuestion::findOrFail($validated['question_id']);
            $category = PapikostickCategory::findOrFail($validated['category_id']);

            $papikostick_option = new PapikostickOption();
            $papikostick_option->content = $validated['content'];
            $papikostick_option->question_id = $question->id;
            $papikostick_option->category_id = $category->id;

            $papikostick_option->save();

            return new PapikostickOptionResource($papikostick_option);            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionResource
     */
    public function show($id)
    {
        $papikostick_option = PapikostickOption::findOrFail($id);

        return new PapikostickOptionResource($papikostick_option);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickOption\UpdatePapikostickOptionRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionResource
     */
    public function update(UpdatePapikostickOptionRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_option = PapikostickOption::findOrFail($id);
            $question = PapikostickQuestion::findOrFail($validated['question_id']);
            $category = PapikostickCategory::findOrFail($validated['category_id']);

            $papikostick_option->content = $validated['content'];
            $papikostick_option->question_id = $question->id;
            $papikostick_option->category_id = $category->id;

            $papikostick_option->save();

            return new PapikostickOptionResource($papikostick_option);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickOption\PapikostickOptionResource
     */
    public function destroy($id)
    {
        $papikostick_option = PapikostickOption::findOrFail($id);
        $papikostick_option->delete();

        return new PapikostickOptionResource($papikostick_option);
    }
}
