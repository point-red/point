<?php

namespace App\Http\Controllers\Api\Plugin\Study;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\Study\StudySheetStoreRequest;
use App\Http\Requests\Plugin\Study\StudySheetUpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\Study\StudySheet;
use App\Services\Google\Drive;
use Illuminate\Http\Request;

class StudySheetController extends Controller
{
    public function __construct()
    {
        // Authorize controller through Policy.
        $this->authorizeResource(StudySheet::class, 'sheet');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request  $request)
    {
        $sheets = StudySheet::eloquentFilter($request)
            ->with(['subject:id,name'])
            ->where('user_id', auth()->id())
            ->paginate();
        
        return new ApiCollection($sheets);
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
     * @param  \App\Http\Requests\Plugin\Study\StudySheetStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StudySheetStoreRequest $request)
    {
        $validated = $request->validated();

        $sheet = new StudySheet();
        $sheet->fill($validated);
        $sheet->user_id = auth()->id();

        // TODO test mock google drive assert called
        $googleDrive = new Drive();

        if ($request->has('photo')) {
            $sheet->photo_file_id = $googleDrive->store($request->file('photo'));
        }
        if ($request->has('audio')) {
            $sheet->audio_file_id = $googleDrive->store($request->file('audio'));
        }
        if ($request->has('video')) {
            $sheet->video_file_id = $googleDrive->store($request->file('video'));
        }
        
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
        $sheet->load('subject:id,name');
        $sheet->append(['photo', 'audio', 'video']);

        return $sheet;
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
     * @param  \App\Http\Requests\Plugin\Study\StudySheetUpdateRequest  $request
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return \Illuminate\Http\Response
     */
    public function update(StudySheetUpdateRequest $request, StudySheet $sheet)
    {
        $validated = $request->validated();
        $sheet->fill($validated);

        // TODO test mock google drive assert called
        $googleDrive = new Drive();

        // if file existed, but request body doesn't have the file id
        // remove the file from drive
        if ($sheet->photo_file_id && !$request->has('photo_file_id')) {
            $googleDrive->destroy($sheet->photo_file_id);
        }
        if ($sheet->audio_file_id && !$request->has('audio_file_id')) {
            $googleDrive->destroy($sheet->audio_file_id);
        }
        if ($sheet->video_file_id && !$request->has('video_file_id')) {
            $googleDrive->destroy($sheet->video_file_id);
        }

        // store photo to drive
        if ($request->has('photo')) {
            $sheet->photo_file_id = $googleDrive->store($request->file('photo'));
        }
        // store voice to drive
        if ($request->has('audio')) {
            $sheet->audio_file_id = $googleDrive->store($request->file('audio'));
        }
        // store video to drive
        if ($request->has('video')) {
            $sheet->video_file_id = $googleDrive->store($request->file('video'));
        }

        $sheet->save();
        
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
        // TODO test mock google drive assert called
        $googleDrive = new Drive();

        // delete photo from drive
        if ($sheet->photo_file_id) {
            $googleDrive->destroy($sheet->photo_file_id);
        }
        // delete voice from drive
        if ($sheet->audio_file_id) {
            $googleDrive->destroy($sheet->audio_file_id);
        }
        // delete video from drive
        if ($sheet->video_file_id) {
            $googleDrive->destroy($sheet->video_file_id);
        }
        
        // delete from database
        return $sheet->delete();
    }
}
