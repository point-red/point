<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Instruction;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PlayBook\Instruction;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    
    public function index($id)
    {
        $instruction = Instruction::findOrFail($id);
        
        if ($instruction) {
            $instruction = $instruction->history;
        }

        # sepertinya gak perlu pakai accesor mutator-nya laravel
        $instruction->name = json_decode($instruction->name);
        $instruction->number = json_decode($instruction->number);
        $instruction->steps = json_decode($instruction->steps);

        return response()->json(compact('instruction'));
    }
}
