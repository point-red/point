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
     * Generate a random string, using a cryptographically secure 
     * pseudorandom number generator (random_int)
     *
     * This function uses type hints now (PHP 7+ only), but it was originally
     * written for PHP 5 as well.
     * 
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     * Taken from: https://stackoverflow.com/a/31107425/7711812
     * 
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    private function random_str(
        int $length = 5,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * 
     */
    public function login(Request $request)
    {
        $candidate = Candidate::where('password', $request->password)->firstOrFail();

        return new CandidateResource($candidate);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\Psychotest\Candidate\CandidateCollection
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
            $candidate->password = $this->random_str();

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
            $candidate->is_password_used = $validated['is_password_used'];

            $candidate->is_kraepelin_started = $validated['is_kraepelin_started'];
            $candidate->is_kraepelin_finished = $validated['is_kraepelin_finished'];

            $candidate->is_papikostick_started = $validated['is_papikostick_started'];
            $candidate->current_papikostick_index = $validated['current_papikostick_index'];
            $candidate->is_papikostick_finished = $validated['is_papikostick_finished'];

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
