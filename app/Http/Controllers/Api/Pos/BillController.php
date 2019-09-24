<?php

namespace App\Http\Controllers\Api\Pos;

use Throwable;
use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Pos\PosBill;
use App\Http\Requests\Pos\PosBill\StorePosBillRequest;
use App\Http\Requests\Pos\PosBill\UpdatePosBillRequest;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $bills = PosBill::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('customer', $fields)) {
                $bills = $bills->leftJoin(Customer::getTableName(), function ($q) {
                    $q->on(Customer::getTableName('id'), '=', PosBill::getTableName('customer_id'));
                });
            }

            if (in_array('form', $fields)) {
                $bills = $bills->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', PosBill::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), PosBill::$morphName);
                });
            }
        }

        $bills = pagination($bills, $request->get('limit'));

        return new ApiCollection($bills);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePosBillRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StorePosBillRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $bill = PosBill::create($request->all());

            $bill
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('services.service');

            return new ApiResource($bill);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $bill = PosBill::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($bill);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePosBillRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdatePosBillRequest $request, $id)
    {
        $bill = PosBill::findOrFail($id);
        $bill->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $bill) {
            $bill->form->archive();
            $request['number'] = $bill->form->edited_number;
            $request['old_increment'] = $bill->form->increment;

            $bill = PosBill::create($request->all());
            $bill->load([
                'form',
                'customer',
                'items.item',
                'services.service',
            ]);

            return new ApiResource($bill);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function destroy(Request $request, $id)
    {
        $bill = PosBill::findOrFail($id);
        $bill->isAllowedToDelete();

        $response = $bill->requestCancel($request);

        return response()->json([], 204);
    }
}
