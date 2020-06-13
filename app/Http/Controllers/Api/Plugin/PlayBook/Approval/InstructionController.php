<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Approval;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Mail\Plugin\PlayBook\Approval\InstructionApprovalRequestSent;
use App\Model\Master\User;
use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\InstructionHistory;
use App\Model\Plugin\PlayBook\InstructionStep;
use App\Model\Plugin\PlayBook\InstructionStepContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            $query->approvalNotSent()->orWhereHas('steps', function ($query) {
                $query->approvalNotSent();
            })->with(['steps' => function ($query) {
                $query->with('contents.glossary')->approvalNotSent();
            }]);
        } else {
            $query->approvalRequested()->orWhereHas('steps', function ($query) {
                $query->approvalRequested();
            })->with(['steps' => function ($query) {
                $query->with('contents.glossary')->approvalRequested();
            }]);
        }

        $instructions = pagination($query, $request->limit ?: 10);

        return new ApiCollection($instructions);
    }

    /**
     * Send approval request to a specific approver.
     */
    public function sendApproval(Request $request)
    {
        $request->validate([
            'approver_id' => ['required', 'numeric'],
        ]);

        Instruction::approvalNotSent()->whereIn('id', $request->ids)->update([
            'approval_request_by' => $request->user()->id,
            'approval_request_at' => now(),
            'approval_request_to' => $request->approver_id,
        ]);

        InstructionStep::approvalNotSent()->whereIn('id', $request->step_ids)->update([
            'approval_request_by' => $request->user()->id,
            'approval_request_at' => now(),
            'approval_request_to' => $request->approver_id,
        ]);

        $approver = User::findOrFail($request->approver_id);

        // send email
        $instructions = Instruction::whereIn('id', $request->ids)->get();
        $steps = InstructionStep::whereIn('id', $request->step_ids)->get();

        foreach ($steps as $step) {
            $instruction = $instructions->firstWhere('id', $step->instruction_id);

            if (!$instruction) {
                $instruction = $step->instruction;
                $instructions->push($instruction);
            }

            if (!$instruction->_steps) {
                $instruction->_steps = collect([]);
            }

            $instruction->_steps->push($step);
        }

        foreach ($instructions as $instruction) {
            Mail::to([
                $approver->email,
            ])->queue(new InstructionApprovalRequestSent(
                $instruction,
                $instruction->_steps,
                $approver,
                $_SERVER['HTTP_REFERER']
            ));
        }

        return [
            'message' => 'good',
        ];
    }

    /**
     * Approve a instruction.
     */
    public function approve(Instruction $instruction)
    {
        if ($instruction->approval_action === 'store') {
            $instruction->update([
                'approved_at' => now(),
            ]);
        } elseif ($instruction->approval_action === 'update') {
            $source = Instruction::findOrFail($instruction->instruction_pending_id);
            InstructionHistory::updateInstruction($instruction->toArray(), $source);
            $source->fill($instruction->toArray());
            $source->update([
                'approved_at' => now(),
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

    /**
     * Decline.
     */
    public function decline(Request $request, Instruction $instruction)
    {
        $instruction->update([
            'approval_note' => $request->approval_note,
            'declined_at' => now(),
        ]);

        return $instruction;
    }

    /**
     * Approve a instruction.
     */
    public function approveStep(Instruction $instruction, InstructionStep $step)
    {
        if ($step->approval_action === 'store') {
            $step->update([
                'approved_at' => now(),
            ]);
        } elseif ($step->approval_action === 'update') {
            $source = InstructionStep::findOrFail($step->instruction_step_pending_id);
            $oldStep = (clone $source);

            $source->fill($step->toArray());
            $source->update([
                'approved_at' => now(),
            ]);
            $source->contents()->delete();

            foreach ($step->contents as $content) {
                $source->contents()->save(new InstructionStepContent($content->toArray()));
            }

            $source->contents = $source->contents()->with('glossary')->get();
            InstructionHistory::updateStep($source, $oldStep);
            $step->delete();

            return $source;
        } elseif ($step->approval_action === 'destroy') {
            $source = InstructionStep::findOrFail($step->instruction_step_pending_id);
            $source->delete();
            InstructionStep::whereInstructionStepPendingId($step->instruction_step_pending_id)->delete();
            $step->delete();
        }

        return $step;
    }

    /**
     * Decline step.
     */
    public function declineStep(Request $request, Instruction $instruction, InstructionStep $step)
    {
        $step->update([
            'approval_note' => $request->approval_note,
            'declined_at' => now(),
        ]);

        return $step;
    }
}
