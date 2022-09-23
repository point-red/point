<?php

namespace App\Http\Controllers\Api\Inventory\InventoryUsage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        
        $result = DB::connection('tenant')->transaction(function () use ($id) {
            $inventoryUsage = InventoryUsage::findOrFail($id);
            
            try {
                $form = $inventoryUsage->form;

                if ($form->approval_status !== 0) {
                    throw new \App\Exceptions\ApprovalNotFoundException();
                }

                $form->approval_by = auth()->user()->id;
                $form->approval_at = now();
                $form->approval_status = 1;
                $form->save();
        
                $inventoryUsage->updateInventory($form, $inventoryUsage);
                $inventoryUsage->updateJournal($inventoryUsage);
    
                $form->fireEventApproved();
            } catch (\Throwable $th) {
                return response_error($th);
            }
    
            return new ApiResource($inventoryUsage);
        });
        

        return $result;
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([ 'reason' => 'required' ]);

        $result = DB::connection('tenant')->transaction(function () use ($request, $validated, $id) {
            $inventoryUsage = InventoryUsage::findOrFail($id);

            $form = $inventoryUsage->form;
            
            if ($form->approval_status !== 0) {
                throw new \App\Exceptions\ApprovalNotFoundException();
            }

            $form->approval_by = auth()->user()->id;
            $form->approval_at = now();
            $form->approval_reason = $validated['reason'];
            $form->approval_status = -1;
            $form->save();
    
            $form->fireEventRejected();
    
            return new ApiResource($inventoryUsage);
        });

        return $result;
    }
}
