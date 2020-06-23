<?php

namespace App\Contracts\Controller;

use App\Http\Requests\CancellationApproval\ApproveRequest;
use App\Http\Requests\CancellationApproval\RejectRequest;

interface CancellationApproval
{
    public function approve(ApproveRequest $request, $id);

    public function reject(RejectRequest $request, $id);
}
