<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseInvoice\PurchaseInvoice;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;

class PurchasePendingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $forms = Form::where(function ($query) {
            $query->where('formable_type', PurchaseRequest::$morphName)
                ->orWhere('formable_type', PurchaseOrder::$morphName)
                ->orWhere('formable_type', PurchaseReceive::$morphName)
                ->orWhere('formable_type', PurchaseInvoice::$morphName);
        })->where('done', false)->with('formable');

        $forms = pagination($forms, $request->get('limit'));

        return new ApiCollection($forms);
    }
}
