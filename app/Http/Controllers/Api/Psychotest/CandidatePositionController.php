<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\CandidatePosition;

use App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionResource;
use App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionCollection;

use App\Http\Requests\Psychotest\CandidatePosition\StoreCandidatePositionRequest;
use App\Http\Requests\Psychotest\CandidatePosition\UpdateCandidatePositionRequest;

class CandidatePositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionCollection
     */
    public function index(Request $request)
    {
        $candidatePositions = CandidatePosition::eloquentFilter($request)->select('psychotest_candidate_positions.*');
        $candidatePositions = pagination($candidatePositions, $request->input('limit'));

        return new CandidatePositionCollection($candidatePositions);
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
     * @param  \App\Http\Requests\Psychotest\CandidatePosition\StoreCandidatePositionRequest  $request
     * @return \App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionResource
     */
    public function store(StoreCandidatePositionRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $candidatePosition = new CandidatePosition();
            $candidatePosition->position = $validated['position'];

            $candidatePosition->save();

            return new CandidatePositionResource($candidatePosition);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionResource
     */
    public function show($id)
    {
        $candidatePosition = CandidatePosition::findOrFail($id);

        return new CandidatePositionResource($candidatePosition);
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
     * @param  \App\Http\Requests\Psychotest\CandidatePosition\UpdateCandidatePositionRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionResource
     */
    public function update(UpdateCandidatePositionRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $candidatePosition = CandidatePosition::findOrFail($id);
            $candidatePosition->position = $validated['position'];

            $candidatePosition->save();

            return new CandidatePositionResource($candidatePosition);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\CandidatePosition\CandidatePositionResource
     */
    public function destroy($id)
    {
        $candidatePosition = CandidatePosition::findOrFail($id);
        $candidatePosition->delete();

        return new CandidatePositionResource($candidatePosition);
    }
}
