<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\Kraepelin;
use App\Model\Psychotest\Candidate;

use App\Http\Resources\Psychotest\Kraepelin\KraepelinResource;
use App\Http\Resources\Psychotest\Kraepelin\KraepelinCollection;

use App\Http\Requests\Psychotest\Kraepelin\StoreKraepelinRequest;
use App\Http\Requests\Psychotest\Kraepelin\UpdateKraepelinRequest;

class KraepelinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Http\Resources\Psychotest\Kraepelin\KraepelinCollection
     */
    public function index(Request $request)
    {
        $kraepelins = Kraepelin::eloquentFilter($request)->select('psychotest_kraepelins.*');
        $kraepelins = pagination($kraepelins, $request->input('limit'));

        return new KraepelinCollection($kraepelins);
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
     * @param  \App\Http\Requests\Psychotest\Kraepelin\StoreKraepelinRequest  $request
     * @return \App\Http\Resources\Psychotest\Kraepelin\KraepelinResource
     */
    public function store(StoreKraepelinRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $candidate = Candidate::findOrFail($validated['candidate_id']);
            $candidate->is_password_used = true;

            $kraepelin = new Kraepelin();
            $kraepelin->candidate_id = $candidate->id;

            $kraepelin->save();
            $candidate->save();

            return new KraepelinResource($kraepelin);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $kraepelin = Kraepelin::findOrFail($id);

        return new KraepelinResource($kraepelin);
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
     * @param  \App\Http\Requests\Psychotest\Kraepelin\UpdateKraepelinRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Kraepelin\KraepelinResource
     */
    public function update(UpdateKraepelinRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $candidate = Candidate::findOrFail($validated['candidate_id']);

            $kraepelin = Kraepelin::findOrFail($id);
            $kraepelin->candidate_id = $candidate->id;
            
            if ($request->filled(['total_count', 'total_correct'])) {
                $kraepelin->total_count = $validated['total_count'];
                $kraepelin->total_correct = $validated['total_correct'];
            }
            
            if ($request->filled('active_column_id')) {
                $kraepelinColumn = KraepelinColumn::findOrFail($validated['active_column_id']);
                
                $kraepelin->active_column_id = $kraepelinColumn->id;
            }

            $kraepelin->save();

            return new KraepelinResource($kraepelin);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Kraepelin\KraepelinResource
     */
    public function destroy($id)
    {
        $kraepelin = Kraepelin::findOrFail($id);
        $kraepelin->delete();

        return new KraepelinResource($kraepelin);
    }
}
