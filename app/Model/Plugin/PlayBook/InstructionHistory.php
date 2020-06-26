<?php

namespace App\Model\Plugin\PlayBook;

use Illuminate\Database\Eloquent\Model;

class InstructionHistory extends Model
{
    protected $connection = 'tenant';

    protected $table = 'play_book_instruction_histories';

    protected $fillable = [
        'instruction_id', 'number', 'name', 'steps', 'status',
    ];

    public function instruction()
    {
        return $this->belongsTo(Instruction::class);
    }

    public static function createHistory(instruction $instruction)
    {
        $steps = [];

        foreach ($instruction->steps()->with('contents.glossary')->get() as $step) {
            $steps[] = (object) [
                'id' => $step->id,
                'histories' => [
                    $step,
                ],
            ];
        }

        $history = new self([
            'number' => json_encode([$instruction->number]),
            'name' => json_encode([$instruction->name]),
            'steps' => json_encode($steps),
        ]);

        $instruction->history()->save(
            $history
        );

        return $history;
    }

    public static function updateInstruction($newValue, Instruction $instruction)
    {
        $history = $instruction->history()->first(); // ben gak dianggep atribute

        if (! $history) {
            $history = self::createHistory($instruction);
        }

        if ($newValue === null) {
            return;
        }

        $instruction = $instruction->toArray();

        foreach (['number', 'name'] as $key) {
            $newHistoryArray = json_decode($history->{$key});
            array_push(
                $newHistoryArray,
                $newValue[$key]
            );
            $history->{$key} = json_encode($newHistoryArray);
        }

        $history->save();
    }

    public static function updateStep($newValue, InstructionStep $step)
    {
        $history = $step->instruction->history()->first(); // ben gak dianggep atribute

        if (! $history) {
            $history = self::createHistory($step->instruction);
        }

        $steps = json_decode($history->steps);
        $index = -1;

        // ambil index array step dari $history->steps
        for ($i = 0; $i < count($steps); $i++) {
            if ($steps[$i]->id == $step->id) {
                $index = $i;
            }
        }

        if ($index > -1) {
            $steps[$index]->histories[] = $newValue;
        } else {
            $steps[] = (object) [
                'id' => $step->id,
                'histories' => [
                    $step,
                ],
            ];
        }

        $history->steps = json_encode($steps);
        $history->save();
    }
}
