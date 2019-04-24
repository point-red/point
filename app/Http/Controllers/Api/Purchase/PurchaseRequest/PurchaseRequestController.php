<?php

namespace App\Http\Controllers\Api\Purchase\PurchaseRequest;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Supplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Http\Requests\Purchase\PurchaseRequest\PurchaseRequest\StorePurchaseRequestRequest;

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
        $purchaseRequests = PurchaseRequest::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('supplier', $fields)) {
                $purchaseRequests = $purchaseRequests->join(Supplier::getTableName(), function ($q) {
                    $q->on(Supplier::getTableName('id'), '=', PurchaseRequest::getTableName('supplier_id'));
                });
            }

            if (in_array('employee', $fields)) {
                $purchaseRequests = $purchaseRequests->join(Employee::getTableName(), function ($q) {
                    $q->on(Employee::getTableName('id'), '=', PurchaseRequest::getTableName('employee_id'));
                });
            }

            if (in_array('form', $fields)) {
                $purchaseRequests = $purchaseRequests->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PurchaseRequest::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PurchaseRequest::class);
                });
            }
        }

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
        $purchaseRequest = PurchaseRequest::eloquentFilter($request)->with('form')->findOrFail($id);

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
        $purchaseRequest = PurchaseRequest::with('form')->findOrFail($id);

        $purchaseRequest->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $purchaseRequest) {
            $purchaseRequest->form->archive();
            $request['number'] = $purchaseRequest->form->edited_number;
            $request['increment'] = $purchaseRequest->form->increment;
            $request['increment_group'] = $purchaseRequest->form->increment_group;

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
