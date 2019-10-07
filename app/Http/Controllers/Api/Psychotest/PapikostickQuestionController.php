<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\PapikostickQuestion;

use App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionCollection;
use App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionResource;

use App\Http\Requests\Psychotest\PapikostickQuestion\StorePapikostickQuestionRequest;
use App\Http\Requests\Psychotest\PapikostickQuestion\UpdatePapikostickQuestionRequest;

class PapikostickQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionCollection
     */
    public function index(Request $request)
    {
        $papikostick_questions = PapikostickQuestion::eloquentFilter($request)->select('psychotest_papikostick_questions.*');
        $papikostick_questions = pagination($papikostick_questions, $request->input('limit'));

        return new PapikostickQuestionCollection($papikostick_questions);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickQuestion\StorePapikostickQuestionRequest  $request
     * @return \App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionResource
     */
    public function store(StorePapikostickQuestionRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_question = new PapikostickQuestion();
            $papikostick_question->number = $validated['number'];
            
            $papikostick_question->save();

            return new PapikostickQuestionResource($papikostick_question);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionResource
     */
    public function show($id)
    {
        $papikostick_question = PapikostickQuestion::findOrFail($id);

        return new PapikostickQuestionResource($papikostick_question);
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
     * @return \App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionResource
     */
    public function update(UpdatePapikostickQuestionRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_question = PapikostickQuestion::findOrFail($id);
            $papikostick_question->number = $validated['number'];

            $papikostick_question->save();

            return new PapikostickQuestionResource($papikostick_question);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickQuestion\PapikostickQuestionResource
     */
    public function destroy($id)
    {
        $papikostick_question = PapikostickQuestion::findOrFail($id);
        $papikostick_question->delete();

        return new PapikostickQuestionResource($papikostick_question);
    }
}
