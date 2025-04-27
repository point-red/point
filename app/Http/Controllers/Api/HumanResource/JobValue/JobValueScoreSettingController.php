<?php

namespace App\Http\Controllers\Api\HumanResource\JobValue;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\JobValue\JobValueScoreSetting\UpdateJobValueScoreSettingRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\HumanResource\JobValue\JobValueScoreSetting;

class JobValueScoreSettingController extends Controller
{
    /**
     * Get Data.
     *
     * @return \App\Http\Resources\ApiCollection
     */
    public function get()
    {
        $master = JobValueScoreSetting::first();
        return new ApiResource($master ?? new JobValueScoreSetting());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\JobValue\Master\UpdateJobValueScoreSettingRequest $request
     * @return \App\Http\Resources\ApiCollection
     */
    public function update(UpdateJobValueScoreSettingRequest $request)
    {
        $score = JobValueScoreSetting::first();

        if ($score) {
            // Update the existing record
            $score->update([
                'minimum_kpi' => $request->input('minimum_kpi'),
                'minimum_coc' => $request->input('minimum_coc'),
            ]);
        } else {
            // Create a new record if none exists
            $score = JobValueScoreSetting::create([
                'minimum_kpi' => $request->input('minimum_kpi'),
                'minimum_coc' => $request->input('minimum_coc'),
            ]);
        }

        return new ApiResource($score);
    }
}