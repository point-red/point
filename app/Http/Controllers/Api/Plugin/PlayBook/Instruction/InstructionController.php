<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstructionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $instructions = Instruction::filter($request)
            ->approved()
            ->orderBy('number')
            ->get();

        return response()->json(compact('instructions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $query = Instruction::latest();

        if ($request->has('procedure_id')) {
            $query->whereProcedureId($request->procedure_id);
        }

        $instruction = $query->first();

        if (! $instruction) {
            return response()->json([
                'number' => null,
            ]);
        }

        $delimiter = '~*~';
        $onlyNumerics = explode(
            $delimiter,
            preg_replace('/[^0-9]/', $delimiter, $instruction->number)
        );
        $lastNumeric = $onlyNumerics[count($onlyNumerics) - 1];
        $nonIteration = substr(
            $instruction->number,
            0,
            strlen($instruction->number) - strlen("{$lastNumeric}")
        );

        return response()->json([
            'number' => $nonIteration.++$lastNumeric,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => ['unique:tenant.play_book_instructions'],
            'name' => ['required'],
            'procedure_id' => ['required', 'numeric'],
        ]);

        $instruction = new Instruction($request->all());
        $instruction->approval_action = 'store';
        $instruction->save();

        InstructionHistory::updateInstruction(null, $instruction);

        return [
            'instruction' => $instruction,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Instruction $instruction)
    {
        return response()->json(compact('instruction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instruction $instruction)
    {
        $request->validate([
            'number' => [
                'required',
                Rule::unique('tenant.play_book_instructions', 'number')
                    ->where(function ($query) use ($instruction) {
                        return $query->whereNotIn('id', [$instruction->id, $instruction->instruction_pending_id]);
                    }),
            ],
            'name' => ['required'],
            'procedure_id' => ['required', 'numeric'],
        ]);

        if ($instruction->approved_at && $instruction->approval_request_at) {
            $approval = new Instruction($request->only('number', 'name'));
            $approval->approval_action = 'update';
            $approval->procedure_id = $instruction->procedure_id;
            $approval->instruction_pending_id = $instruction->id;
            $approval->save();
        } else {
            $instruction->update($request->all());
        }

        return response()->json(compact('instruction'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instruction $instruction)
    {
        if ($instruction->declined_at) {
            $instruction->delete();

            return ['message' => 'deleted'];
        }

        $approval = new Instruction([
            'procedure_id' => $instruction->procedure_id,
            'number' => $instruction->number,
            'name' => $instruction->name,
        ]);
        $approval->approval_action = 'destroy';
        $approval->instruction_pending_id = $instruction->id;
        $approval->save();

        return response()->json([
            'message' => 'Deleted.',
        ]);
    }
}
