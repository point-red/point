<?php

namespace App\Http\Controllers\Api\Plugin\Study;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\Study\StudySubjectRequest;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\Study\StudySubject;

class StudySubjectController extends Controller
{
    public function __construct()
    {
        // Authorize controller through Policy.
        $this->authorizeResource(StudySubject::class, 'subject');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subjects = StudySubject::orderBy('name')->get();
        
        return new ApiCollection($subjects);
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
     * @param  \App\Http\Requests\Plugin\Study\StudySubjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StudySubjectRequest $request)
    {
        $validated = $request->validated();

        return StudySubject::create($validated);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return \Illuminate\Http\Response
     */
    public function show(StudySubject $subject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return \Illuminate\Http\Response
     */
    public function edit(StudySubject $subject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Plugin\Study\StudySubjectRequest  $request
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return \Illuminate\Http\Response
     */
    public function update(StudySubjectRequest $request, StudySubject $subject)
    {
        $validated = $request->validated();

        $subject->update($validated);
        
        return $subject;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudySubject $subject)
    {
        $subject->delete();
    }
}
