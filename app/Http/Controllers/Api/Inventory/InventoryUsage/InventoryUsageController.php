<?php

namespace App\Http\Controllers\Api\Inventory\InventoryUsage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\Usage\StoreRequest;
use App\Http\Requests\Inventory\Usage\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $inventoryUsages = InventoryUsage::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('form', $fields)) {
                $inventoryUsages = $inventoryUsages->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', InventoryUsage::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), InventoryUsage::$morphName);
                });
            }
        }

        $inventoryUsages = pagination($inventoryUsages, $request->get('limit'));

        return new ApiCollection($inventoryUsages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(StoreRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $inventoryUsage = InventoryUsage::create($request->all());
            $inventoryUsage
                ->load('form')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($inventoryUsage);
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
        $inventoryUsage = InventoryUsage::eloquentFilter($request)->with('form.createdBy')->findOrFail($id);

        if ($request->has('with_archives')) {
            $inventoryUsage->archives = $inventoryUsage->archives();
        }

        if ($request->has('with_origin')) {
            $inventoryUsage->origin = $inventoryUsage->origin();
        }

        return new ApiResource($inventoryUsage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return ApiResource
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, $id)
    {
        $inventoryUsage = InventoryUsage::with('form')->findOrFail($id);

        $inventoryUsage->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $inventoryUsage) {
            $inventoryUsage->form->archive();
            $request['number'] = $inventoryUsage->form->edited_number;
            $request['old_increment'] = $inventoryUsage->form->increment;

            $inventoryUsage = InventoryUsage::create($request->all());
            $inventoryUsage
                ->load('form')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($inventoryUsage);
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
        $inventoryUsage = InventoryUsage::findOrFail($id);

        $inventoryUsage->isAllowedToDelete();

        $inventoryUsage->requestCancel($request);

        return response()->json([], 204);
    }
}
