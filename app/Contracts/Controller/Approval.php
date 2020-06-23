<?php

namespace App\Contracts\Controller;

use App\Http\Requests\Approval\ApproveRequest;
use App\Http\Requests\Approval\RejectRequest;

interface Approval
{
    public function approve(ApproveRequest $request, $id);

    public function reject(RejectRequest $request, $id);
}
