<?php

namespace App\Http\Controllers\Api\HumanResource\Psychology;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\HumanResource\Psychology\Kraeplin;
use App\Model\HumanResource\Psychology\Candidate;

use App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinResource;
use App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinCollection;

use App\Http\Requests\HumanResource\Psychology\Kraeplin\StoreKraeplinRequest;
use App\Http\Requests\HumanResource\Psychology\Kraeplin\UpdateKraeplinRequest;

class KraeplinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinCollection
     */
    public function index()
    {
        return new KraeplinCollection(Kraeplin::all());
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
     * @param  \App\Http\Requests\HumanResource\Psychology\Kraeplin\StoreKraeplinRequest  $request
     * @return \App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinResource
     */
    public function store(StoreKraeplinRequest $request)
    {
        $candidate = Candidate::findOrFail($request->input('candidate_id'));
        // $kraeplinColumn = 

        $kraeplin = new Kraeplin();
        $kraeplin->candidate_id = $candidate->id;
        $kraeplin->column_duration = $request->input('column_duration');
        $kraeplin->total_count = $request->input('total_count');
        $kraeplin->total_correct = $request->input('total_correct');
        $kraeplin->active_column_id = $request->input('active_column_id');
        $kraeplin->save();

        return new KraeplinResource($kraeplin);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $kraeplin = Kraeplin::findOrFail($id);

        return new KraeplinResource($kraeplin);
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
     * @param  \App\Http\Requests\HumanResource\Psychology\Kraeplin\UpdateKraeplinRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinResource
     */
    public function update(UpdateKraeplinRequest $request, $id)
    {
        $candidate = Candidate::findOrFail($request->input('candidate_id'));

        $kraeplin = Kraeplin::findOrFail($id);
        $kraeplin->candidate_id = $candidate->id;
        $kraeplin->column_duration = $request->input('column_duration');
        $kraeplin->total_count = $request->input('total_count');
        $kraeplin->total_correct = $request->input('total_correct');
        $kraeplin->active_column_id = $request->input('active_column_id');
        $kraeplin->save();

        return new KraeplinResource($kraeplin);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\Kraeplin\KraeplinResource
     */
    public function destroy($id)
    {
        $kraeplin = Kraeplin::findOrFail($id);
        $kraeplin->delete();

        return new KraeplinResource($kraeplin);
    }
}
