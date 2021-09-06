<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseInvoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Traits\Model\Purchase\PurchaseInvoiceJoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceApprovalController extends Controller
{
    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function approve(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        if ($purchaseInvoice->form->approval_status === 0) {
            $purchaseInvoice->form->approval_by = auth()->user()->id;
            $purchaseInvoice->form->approval_at = now();
            $purchaseInvoice->form->approval_status = 1;
            $purchaseInvoice->form->save();

            PurchaseInvoice::updateInventory($purchaseInvoice->form, $purchaseInvoice);
            PurchaseInvoice::updateJournal($purchaseInvoice);
        }

        DB::connection('tenant')->commit();

        return new ApiResource($purchaseInvoice);
    }

    /**
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function reject(Request $request, $id)
    {
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $purchaseInvoice->form->approval_by = auth()->user()->id;
        $purchaseInvoice->form->approval_at = now();
        $purchaseInvoice->form->approval_reason = $request->get('reason');
        $purchaseInvoice->form->approval_status = -1;
        $purchaseInvoice->form->save();

        return new ApiResource($purchaseInvoice);
    }
}
