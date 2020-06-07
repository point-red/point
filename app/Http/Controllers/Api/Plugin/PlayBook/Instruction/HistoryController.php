<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PlayBook\Instruction;

class HistoryController extends Controller
{
    public function index($id)
    {
        $instruction = Instruction::findOrFail($id);

        if ($instruction) {
            $instruction = $instruction->history;
        }

        // sepertinya gak perlu pakai accesor mutator-nya laravel
        $instruction->name = json_decode($instruction->name);
        $instruction->number = json_decode($instruction->number);
        $instruction->steps = json_decode($instruction->steps);
        $instruction->procedure = $instruction->instruction->procedure;

        return response()->json(compact('instruction'));
    }
}
