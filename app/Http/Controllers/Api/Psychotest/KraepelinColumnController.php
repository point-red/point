<?php

namespace App\Http\Controllers\Api\Psychotest;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Psychotest\Kraepelin;
use App\Model\Psychotest\KraepelinColumn;

use App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnResource;
use App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnCollection;

use App\Http\Requests\Psychotest\KraepelinColumn\StoreKraepelinColumnRequest;
use App\Http\Requests\Psychotest\KraepelinColumn\UpdateKraepelinColumnRequest;

class KraepelinColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new KraepelinColumnCollection(KraepelinColumn::all());
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
     * @param  \App\Http\Requests\Psychotest\KraepelinColumn\StoreKraepelinColumnRequest  $request
     * @return \App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnResource
     */
    public function store(StoreKraepelinColumnRequest $request)
    {
        $validated = $request->validated();

        if ($validated) {
            $kraepelin = Kraepelin::findOrFail($validated['kraepelin_id']);

            $kraepelin_column = new KraepelinColumn();
            $kraepelin_column->kraepelin_id = $kraepelin->id;

            $kraepelin_column->current_first_number = random_int(1, 9);
            $kraepelin_column->current_second_number = random_int(1, 9);

            $kraepelin_column->save();
            
            return new KraepelinColumnResource($kraepelin_column);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnResource
     */
    public function show($id)
    {
        $kraepelin_column = KraepelinColumn::findOrFail($id);

        return new KraepelinColumnResource($kraepelin_column);
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
     * @param  \App\Http\Requests\Psychotest\KraepelinColumn\UpdateKraepelinColumnRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnResource
     */
    public function update(UpdateKraepelinColumnRequest $request, $id)
    {
        $validated = $request->validated();

        if ($validated) {
            $kraepelin = Kraepelin::findOrFail($validated['kraepelin_id']);
            $kraepelin_column = KraepelinColumn::findOrFail($id);
            /**
             * If kraepelin created at + kraepelin column duration is greater than current time,
             * it means kraepelin column is still running. Otherwise move into next kraepelin column.
             */
            if (
                Carbon::parse($kraepelin_column->created_at)->
                    addMilliseconds($kraepelin->column_duration)->
                    gt(Carbon::now())
                ) {
                $kraepelin->active_column_id = $id;

                if (($kraepelin_column->current_first_number + $kraepelin_column->current_second_number) % 10 == $validated['answer']) {
                    $kraepelin_column->correct = $kraepelin_column->correct + 1;
                    $kraepelin->total_correct = $kraepelin->total_correct + 1;
    
                    $kraepelin_column->current_first_number = random_int(1, 9);
                    $kraepelin_column->current_second_number = random_int(1, 9);
                    
                    $kraepelin_column->count = $kraepelin_column->count + 1;
                    $kraepelin->total_count = $kraepelin->total_count + 1;
    
                    $kraepelin->save();
                    $kraepelin_column->save();
                    
                    return response()->json(['data' => [
                        'resource' => new KraepelinColumnResource($kraepelin_column),
                        'is_correct' => true,
                        'is_new_column' => false
                    ]]);
                } else {
                    $kraepelin_column->current_first_number = random_int(1, 9);
                    $kraepelin_column->current_second_number = random_int(1, 9);
                    
                    $kraepelin_column->count = $kraepelin_column->count + 1;
                    $kraepelin->total_count = $kraepelin->total_count + 1;
    
                    $kraepelin->save();
                    $kraepelin_column->save();
    
                    return response()->json(['data' => [
                        'resource' => new KraepelinColumnResource($kraepelin_column),
                        'is_correct' => false,
                        'is_new_column' => false
                    ]]);
                }
            } else {
                $kraepelin_column = new KraepelinColumn();
                $kraepelin_column->kraepelin_id = $kraepelin->id;

                $kraepelin_column->current_first_number = random_int(1, 9);
                $kraepelin_column->current_second_number = random_int(1, 9);

                $kraepelin_column->save();

                $kraepelin->active_column_id = $kraepelin_column->id;
                $kraepelin->save();
                
                return response()->json(['data' => [
                    'resource' => new KraepelinColumnResource($kraepelin_column),
                    'is_correct' => false,
                    'is_new_column' => true
                ]]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Psychotest\KraepelinColumn\KraepelinColumnResource
     */
    public function destroy($id)
    {
        $kraepelin_column = KraepelinColumn::findOrFail($id);
        $kraepelin_column->delete();

        return new KraepelinColumnResource($kraepelin_column);
    }
}
