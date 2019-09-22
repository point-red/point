<?php

namespace App\Http\Controllers\Api\HumanResource\Psychology;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\HumanResource\Psychology\Candidate;

use App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource;
use App\Http\Resources\HumanResource\Psychology\Candidate\CandidateCollection;

use App\Http\Requests\HumanResource\Psychology\Candidate\StoreCandidateRequest;
use App\Http\Requests\HumanResource\Psychology\Candidate\UpdateCandidateRequest;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\HumanResource\Psychology\Candidate\CandidateCollection
     */
    public function index()
    {
        return new CandidateCollection(Candidate::all());
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
     * @param  \App\Http\Requests\HumanResource\Psychology\Candidate\StoreCandidateRequest  $request
     * @return \App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource
     */
    public function store(StoreCandidateRequest $request)
    {
        $candidate = new Candidate();
        $candidate->name = $request->input('name');
        $candidate->phone = $request->input('phone');
        $candidate->password = $request->input('password');
        $candidate->is_password_used = $request->input('is_password_used');
        $candidate->save();

        return new CandidateResource($candidate);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource
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
     * @param  \App\Http\Requests\HumanResource\Psychology\Candidate\UpdateCandidateRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource
     */
    public function update(UpdateCandidateRequest $request, $id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->name = $request->input('name');
        $candidate->phone = $request->input('phone');
        $candidate->password = $request->input('password');
        $candidate->is_password_used = $request->input('is_password_used');
        $candidate->save();

        return new CandidateResource($candidate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\HumanResource\Psychology\Candidate\CandidateResource
     */
    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();

        return new CandidateResource($candidate);
    }
}
