<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Helpers\Inventory\InventoryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputMaterialCancellationApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $input = ManufactureInput::findOrFail($id);
        $input->form->cancellation_approval_by = auth()->user()->id;
        $input->form->cancellation_approval_at = now();
        $input->form->cancellation_status = 1;
        $input->form->save();

        InventoryHelper::delete($input->form->id);

        DB::connection('tenant')->commit();

        return new ApiResource($input);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $input = ManufactureInput::findOrFail($id);
        $input->form->cancellation_approval_by = auth()->user()->id;
        $input->form->cancellation_approval_at = now();
        $input->form->cancellation_reason = $request->get('cancellation_reason');
        $input->form->cancellation_status = -1;
        $input->form->save();

        return new ApiResource($input);
    }
}
