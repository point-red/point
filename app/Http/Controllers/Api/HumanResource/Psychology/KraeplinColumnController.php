<?php

namespace App\Http\Controllers\Api\HumanResource\Psychology;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\HumanResource\Psychology\Kraeplin;
use App\Model\HumanResource\Psychology\KraeplinColumn;

use App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource;
use App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnCollection;

use App\Http\Requests\HumanResource\Psychology\KraeplinColumn\StoreKraeplinColumnRequest;
use App\Http\Requests\HumanResource\Psychology\KraeplinColumn\UpdateKraeplinColumnRequest;

class KraeplinColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new KraeplinColumnCollection(KraeplinColumn::all());
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
     * @param  \App\Http\Requests\HumanResource\Psychology\KraeplinColumn\StoreKraeplinColumnRequest  $request
     * @return \App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource
     */
    public function store(StoreKraeplinColumnRequest $request)
    {
        $kraeplin = Kraeplin::findOrFail($request->input('kraeplin_id'));

        $kraeplinColumn = new KraeplinColumn();
        $kraeplinColumn->kraeplin_id = $kraeplin->id;
        $kraeplinColumn->current_first_number = $request->input('current_first_number');
        $kraeplinColumn->current_second_number = $request->input('current_second_number');
        $kraeplinColumn->correct = $request->input('correct');
        $kraeplinColumn->save();
        
        return new KraeplinColumnResource($kraeplinColumn);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource
     */
    public function show($id)
    {
        $kraeplinColumn = KraeplinColumn::findOrFail($id);

        return new KraeplinColumnResource($kraeplinColumn);
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
     * @param  \App\Http\Requests\HumanResource\Psychology\KraeplinColumn\UpdateKraeplinColumnRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource
     */
    public function update(UpdateKraeplinColumnRequest $request, $id)
    {
        $kraeplin = Kraeplin::findOrFail($request->input('kraeplin_id'));

        $kraeplinColumn = KraeplinColumn::findOrFail($id);
        $kraeplinColumn->kraeplin_id = $kraeplin->id;
        $kraeplinColumn->current_first_number = $request->input('current_first_number');
        $kraeplinColumn->current_second_number = $request->input('current_second_number');
        $kraeplinColumn->correct = $request->input('correct');
        $kraeplinColumn->save();
        
        return new KraeplinColumnResource($kraeplinColumn);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\KraeplinColumn\KraeplinColumnResource
     */
    public function destroy($id)
    {
        $kraeplinColumn = KraeplinColumn::findOrFail($id);
        $kraeplinColumn->delete();

        return new KraeplinColumnResource($kraeplinColumn);
    }
}
