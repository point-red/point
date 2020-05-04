<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Approval;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionHistory;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Instruction::with('approver', 'procedure')
            ->filter($request)->orderBy('number');

        if ($request->want === 'send') {
            $query->approvalNotSent();
        } else {
            $query->approvalRequested();
        }

        $instructions = pagination($query, $request->limit ?: 10);

        return new ApiCollection($instructions);
    }

    /**
     * Send approval request to a specific approver
     */
    public function sendApproval(Request $request)
    {
        $instructions = Instruction::approvalNotSent()->whereIn('id', $request->ids)->update([
            'approval_request_by' => $request->user()->id,
            'approval_request_at' => now(),
            'approval_request_to' => $request->approver_id
        ]);

        return [
            'input' => $request->all()
        ];
    }

    /**
     * Approve a instruction
     */
    public function approve(Instruction $instruction)
    {
        if ($instruction->approval_action === 'store') {
            $instruction->update([
                'approved_at' => now()
            ]);
        } elseif ($instruction->approval_action === 'update') {
            $source = Instruction::findOrFail($instruction->instruction_pending_id);
            InstructionHistory::updateInstruction($instruction->toArray(), $source);
            $source->fill($instruction->toArray());
            $source->update([
                'approved_at' => now()
            ]);
            $instruction->delete();

            return $source;
        } elseif ($instruction->approval_action === 'destroy') {
            $source = Instruction::findOrFail($instruction->instruction_pending_id);
            $source->delete();
            Instruction::whereInstructionPendingId($instruction->instruction_pending_id)->delete();
            $instruction->delete();
        }

        return $instruction;
    }
}
