<?php

namespace App\Http\Controllers\Api\Psychotest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\Candidate;

use App\Http\Resources\Psychotest\Candidate\CandidateResource;
use App\Http\Resources\Psychotest\Candidate\CandidateCollection;

use App\Http\Requests\Psychotest\Candidate\StoreCandidateRequest;
use App\Http\Requests\Psychotest\Candidate\UpdateCandidateRequest;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Http\Resources\Psychotest\Candidate\CandidateCollection
     */
    public function index(Request $request)
    {
        $candidates = Candidate::eloquentFilter($request)->select('psychotest_candidates.*');
        $candidates = pagination($candidates, $request->input('limit'));

        return new CandidateCollection($candidates);
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
     * @param  \App\Http\Requests\Psychotest\Candidate\StoreCandidateRequest  $request
     * @return \App\Http\Resources\Psychotest\Candidate\CandidateResource
     */
    public function store(StoreCandidateRequest $request)
    {
        $validated = $request->validated();
        
        if ($validated) {
            $candidate = new Candidate();
            $candidate->name = $validated['name'];
            $candidate->phone = $validated['phone'];
            $candidate->password = $validated['password'];
            
            if ($request->filled('is_password_used')) {
                $candidate->is_password_used = $validated['is_password_used'];
            }

            $candidate->save();

            return new CandidateResource($candidate);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Candidate\CandidateResource
     */
    public function show($id)
    {
        $candidate = Candidate::findOrFail($id);
        
        return new CandidateResource($candidate);
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
     * @param  \App\Http\Requests\Psychotest\Candidate\UpdateCandidateRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Candidate\CandidateResource
     */
    public function update(UpdateCandidateRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $candidate = Candidate::findOrFail($id);
            $candidate->name = $validated['name'];
            $candidate->phone = $validated['phone'];
            $candidate->password = $validated['password'];
            
            if ($request->filled('is_password_used')) {
                $candidate->is_password_used = $validated['is_password_used'];
            }

            $candidate->save();

            return new CandidateResource($candidate);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\Candidate\CandidateResource
     */
    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();

        return new CandidateResource($candidate);
    }
}
