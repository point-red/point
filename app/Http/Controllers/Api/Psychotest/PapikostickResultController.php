<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\PapikostickResult;
use App\Model\Psychotest\Papikostick;
use App\Model\Psychotest\PapikostickCategory;

use App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultCollection;
use App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultResource;

use App\Http\Requests\Psychotest\PapikostickResult\StorePapikostickResultRequest;
use App\Http\Requests\Psychotest\PapikostickResult\UpdatePapikostickResultRequest;

class PapikostickResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultCollection
     */
    public function index(Request $request)
    {
        $papikostick_results = PapikostickResult::from('psychotest_papikostick_results' . ' as ' . 'psychotest_papikostick_result')
            ->join('psychotest_papikosticks as psychotest_papikostick', 'psychotest_papikostick.id', '=', 'psychotest_papikostick_result.papikostick_id')
            ->eloquentFilter($request)
            ->select('psychotest_papikostick_result.*');
        $papikostick_results = pagination($papikostick_results, $request->input('limit'));

        return new PapikostickResultCollection($papikostick_results);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickResult\StorePapikostickResultRequest  $request
     * @return \App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultResource
     */
    public function store(StorePapikostickResultRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_result = new PapikostickResult();
            $papikostick = Papikostick::findOrFail($validated['papikostick_id']);
            $category = PapikostickCategory::findOrFail($validated['category_id']);

            $papikostick_result->total = $validated['total'];
            $papikostick_result->papikostick_id = $papikostick->id;
            $papikostick_result->category_id = $category->id;

            $papikostick_result->save();

            return new PapikostickResultResource($papikostick_result);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultResource
     */
    public function show($id)
    {
        $papikostick_result = PapikostickResult::findOrFail($id);

        return new PapikostickResultResource($papikostick_result);
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
     * @param  \App\Http\Requests\Psychotest\PapikostickResult\UpdatePapikostickResultRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultResource
     */
    public function update(UpdatePapikostickResultRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $papikostick_result = PapikostickResult::findOrFail($id);
            $papikostick = Papikostick::findOrFail($validated['papikostick_id']);
            $category = PapikostickCategory::findOrFail($validated['category_id']);

            $papikostick_result->total = $validated['total'];
            $papikostick_result->papikostick_id = $papikostick->id;
            $papikostick_result->category_id = $category->id;

            $papikostick_result->save();

            return new PapikostickResultResource($papikostick_result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\PapikostickResult\PapikostickResultResource
     */
    public function destroy($id)
    {
        $papikostick_result = PapikostickResult::findOrFail($id);
        $papikostick_result->delete();

        return new PapikostickResultResource($papikostick_result);
    }
}
