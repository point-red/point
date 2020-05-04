<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook\Approval;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Instruction;
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
        $query = Instruction::with('approver')
            ->filter($request)->orderBy('number');

        if ($request->want === 'send') {
            $query->approvalNotSent();
        } else {
            $query->approvalRequested();
        }

        $procedures = pagination($query, $request->limit ?: 10);

        return new ApiCollection($procedures);
    }

    /**
     * Send approval request to a specific approver
     */
    public function sendApproval(Request $request)
    {
        $procedures = Instruction::approvalNotSent()->whereIn('id', $request->ids)->update([
            'approval_request_by' => $request->user()->id,
            'approval_request_at' => now(),
            'approval_request_to' => $request->approver_id
        ]);

        return [
            'input' => $request->all()
        ];
    }
}
