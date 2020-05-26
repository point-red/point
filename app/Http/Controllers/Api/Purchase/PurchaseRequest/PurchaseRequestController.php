<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\PurchaseRequest\PurchaseRequest\StorePurchaseRequestRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Traits\Model\Purchase\PurchaseRequestJoin;
use App\Traits\Model\Purchase\PurchaseRequestRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $purchaseRequests = PurchaseRequest::from(PurchaseRequest::getTableName() . ' as ' . PurchaseRequest::$alias)->eloquentFilter($request);

        $purchaseRequests = PurchaseRequest::joins($purchaseRequests, $request->get('join'));

        $purchaseRequests = pagination($purchaseRequests, $request->get('limit'));

        return new ApiCollection($purchaseRequests);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - required_date (Date)
     *  - number (String)
     *  - date (Date)
     *  - required_date (Date)
     *  - employee_id (Int)
     *  - supplier_id (Int, Optional)
     *  - items (Array) :
     *      - item_id (Int)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *      - price (Decimal)
     *      - description (String Optional)
     *      - allocation_id (Int Optional)
     *  - services (Array) :
     *      - service_id (Int)
     *      - quantity (Decimal)
     *      - price (Decimal)
     *      - description (String Optional)
     *      - allocation_id (Int Optional).
     *
     * @param StorePurchaseRequestRequest $request
     * @return ApiResource
     * @throws \Throwable
     */
    public function store(StorePurchaseRequestRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $purchaseRequest = PurchaseRequest::create($request->all());
            $purchaseRequest
                ->load('form')
                ->load('employee')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseRequest);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::from(PurchaseRequest::getTableName() . ' as ' . PurchaseRequest::$alias)->eloquentFilter($request);

        $purchaseRequest = PurchaseRequest::joins($purchaseRequest, $request->get('join'));

        $purchaseRequest = $purchaseRequest->with('form.createdBy')->where(PurchaseRequest::$alias . '.id', $id)->first();

        if ($request->has('with_archives')) {
            $purchaseRequest->archives = $purchaseRequest->archives();
        }

        if ($request->has('with_origin')) {
            $purchaseRequest->origin = $purchaseRequest->origin();
        }

        return new ApiResource($purchaseRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return ApiResource
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::from(PurchaseRequest::getTableName() . ' as ' . PurchaseRequest::$alias)
            ->joinForm()
            ->where(PurchaseRequest::$alias . '.id', $id)
            ->select(PurchaseRequest::$alias . '.*')
            ->with('form')
            ->first();

        $purchaseRequest->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseRequest) {
            $purchaseRequest->form->archive();
            $request['number'] = $purchaseRequest->form->edited_number;
            $request['old_increment'] = $purchaseRequest->form->increment;

            $purchaseRequest = PurchaseRequest::create($request->all());
            $purchaseRequest
                ->load('form')
                ->load('employee')
                ->load('supplier')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($purchaseRequest);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->isAllowedToDelete();
        $purchaseRequest->requestCancel($request);

        return response()->json([], 204);
    }
}
