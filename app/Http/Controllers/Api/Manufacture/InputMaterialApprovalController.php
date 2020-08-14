<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Exceptions\ProductionNumberNotExistException;
use App\Helpers\Inventory\InventoryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputMaterialApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws ProductionNumberNotExistException
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $input = ManufactureInput::findOrFail($id);
        $input->form->approval_by = auth()->user()->id;
        $input->form->approval_at = now();
        $input->form->approval_status = 1;
        $input->form->save();

        InventoryHelper::posting($input->form->id);

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
        $input->form->approval_by = auth()->user()->id;
        $input->form->approval_at = now();
        $input->form->approval_reason = $request->get('approval_reason');
        $input->form->approval_status = -1;
        $input->form->save();

        return new ApiResource($input);
    }
}
