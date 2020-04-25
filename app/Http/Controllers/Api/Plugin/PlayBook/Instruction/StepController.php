<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionHistory;
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

        $step->contents = $step->contents()->with('glossary')->get(); # load glossary;
        InstructionHistory::updateStep($step, $step);

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instruction $instruction, InstructionStep $step)
    {
        $request->validate([
            'instruction_id' => ['required', 'numeric'],
            'name' => ['required'],
            'contents.*.glossary_id' => ['required'],
            'contents.*.content' => ['required']
        ]);

        $oldStep = (clone $step);

        $step->update($request->only('name'));
        $step->contents()->delete();

        foreach ($request->contents as $content) {
            $step->contents()->save(new InstructionStepContent($content));
        }

        $step->contents = $step->contents()->with('glossary')->get();
        InstructionHistory::updateStep($step, $oldStep);

        return response()->json(compact('step'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instruction $instruction, InstructionStep $step)
    {
        $step->contents()->delete();
        $step->delete();
    }
}
