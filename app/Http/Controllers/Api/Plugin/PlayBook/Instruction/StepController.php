<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionHistory;
use App\Model\Plugin\PlayBook\InstructionStep;
use App\Model\Plugin\PlayBook\InstructionStepContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Instruction $instruction)
    {
        $query = $instruction
            ->steps()
            ->select()
            ->addSelect(
                DB::raw(
                    'case
                        when instruction_step_pending_id is null then id
                        else instruction_step_pending_id
                    end as group_id'
                )
            )
            ->with('contents.glossary')
            ->where(function ($query) use ($request) {
                $query->approved();
                if ($request->is_dirty) {
                    $query->orWhere(function ($query) {
                        $query->approvalNotSent();
                    });
                }

                if ($request->review) {
                    $query->orWhere(function ($query) use ($request) {
                        $query
                            ->notApprovedYet()
                            ->WhereIn('id', $request->review);
                    });

                }
            })
            ->orderBy('group_id');

        $steps = pagination($query, $request->limit ?: 10);

        return new ApiCollection($steps);
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
            'contents.*.content' => ['required'],
        ]);

        $step = new InstructionStep($request->only('name'));
        $step->approval_action = 'store';
        $instruction->steps()->save($step);

        foreach ($request->contents as $content) {
            $step->contents()->save(new InstructionStepContent($content));
        }

        $step->contents = $step->contents()->with('glossary')->get(); // load glossary;
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
            'contents.*.content' => ['required'],
        ]);

        if ($step->approved_at && $step->approval_request_at) {
            $approval = new InstructionStep($request->only('name'));
            $approval->approval_action = 'update';
            $approval->instruction_id = $instruction->id;
            $approval->instruction_step_pending_id = $step->id;
            $approval->save();

            foreach ($request->contents as $content) {
                $approval->contents()->save(new InstructionStepContent($content));
            }
        } else {
            $step->update($request->all());

            $step->contents()->delete();

            foreach ($request->contents as $content) {
                $step->contents()->save(new InstructionStepContent($content));
            }
        }

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
        if ($step->declined_at) {
            $step->contents()->delete();
            $step->delete();

            return ['message' => 'deleted'];
        }

        $approval = new InstructionStep([
            'name' => $step->name,
            'instruction_id' => $step->instruction_id,
            'approval_action' => 'destroy',
            'instruction_step_pending_id' => $step->id,
        ]);

        $approval->save();

        return $approval;
    }
}
