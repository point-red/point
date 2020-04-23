<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionStep;
use App\Model\Plugin\PlayBook\InstructionStepContent;
use Illuminate\Http\Request;

class StepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Instruction $instruction)
    {
        $query = $instruction->steps()->with('contents');
        $steps = pagination($query, $request->limit ?: 10);

        return new ApiCollection($steps);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Instruction $instruction)
    {
        $request->validate([
            'instruction_id' => ['required', 'numeric'],
            'name' => ['required'],
            'contents.*.glossary_id' => ['required'],
            'contents.*.content' => ['required']
        ]);

        $step = new InstructionStep($request->only('name'));
        $instruction->steps()->save($step);

        foreach ($request->contents as $content) {
            $step->contents()->save(new InstructionStepContent($content));
        }

        $step->contents;

        return response()->json(compact('step'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
