<?php

namespace App\Http\Controllers\Api\Plugin\Study;

use App\Http\Controllers\Controller;
use App\Model\Master\User;
use App\Model\Plugin\Study\StudySheet;
use Illuminate\Http\Request;

class StudySheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request  $request)
    {
        return StudySheet::eloquentFilter($request)
            ->fields($request->get('fields'))
            ->where('user_id', auth()->id())
            ->paginate();
        
        // search
        // filter
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $request->file('video');
        // $request->file('voice');
        // $request->file('photo');

        $validated = $request->all();
        // $validated = $request->validated();

        $sheet = new StudySheet();
        $sheet->fill($validated);
        $sheet->user_id = auth()->id();
        $sheet->save();

        return $sheet;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return \Illuminate\Http\Response
     */
    public function show(StudySheet $sheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return \Illuminate\Http\Response
     */
    public function edit(StudySheet $sheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudySheet $sheet)
    {
        $validated = $request->all();
        // $validated = $request->validated();
        $sheet->update($validated);
        return $sheet;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudySheet $sheet)
    {
        return $sheet->delete();

        // delete photo from drive
        // delete voice from drive
        // delete video from drive
        // delete from database
    }
}
