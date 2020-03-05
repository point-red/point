<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manufacture\ManufactureInput\StoreManufactureInputRequest;
use App\Http\Requests\Manufacture\ManufactureInput\UpdateManufactureInputRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Manufacture\ManufactureInput\ManufactureInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class InputMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $inputs = ManufactureInput::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('form', $fields)) {
                $inputs = $inputs->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', ManufactureInput::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), ManufactureInput::$morphName);
                });
            }
        }

        $inputs = pagination($inputs, $request->get('limit'));

        return new ApiCollection($inputs);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - manufacture_machine_id (Int)
     *  - manufacture_process_id (Int)
     *  - manufacture_machine_name (String)
     *  - manufacture_process_name (String)
     *  -
     *  - raw_materials (Array) :
     *      - item_id (Int)
     *      - warehouse_id (Int)
     *      - item_name (String)
     *      - warehouse_name (String)
     *      - quantity (Decimal)
     *      - unit (String)
     *  - finished_goods (Array) :
     *      - item_id (Int)
     *      - warehouse_id (Int)
     *      - item_name (String)
     *      - warehouse_name (String)
     *      - quantity (Decimal)
     *      - unit (String).
     *
     * @param StoreManufactureInputRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreManufactureInputRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $manufactureInput = ManufactureInput::create($request->all());
            $manufactureInput
                ->load('form')
                ->load('manufactureMachine')
                ->load('manufactureProcess')
                ->load('rawMaterials.item')
                ->load('rawMaterials.warehouse')
                ->load('finishedGoods.item')
                ->load('finishedGoods.warehouse');

            return new ApiResource($manufactureInput);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $input = ManufactureInput::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($input);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateManufactureInputRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateManufactureInputRequest $request, $id)
    {
        $manufactureInput = ManufactureInput::findOrFail($id);
        $manufactureInput->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $manufactureInput) {
            $manufactureInput->form->archive();
            $request['number'] = $manufactureInput->form->edited_number;
            $request['old_increment'] = $manufactureInput->form->increment;

            $manufactureInput = ManufactureInput::create($request->all());
            $manufactureInput
                ->load('form')
                ->load('manufactureMachine')
                ->load('manufactureProcess')
                ->load('rawMaterials.item')
                ->load('rawMaterials.warehouse')
                ->load('finishedGoods.item')
                ->load('finishedGoods.warehouse');

            return new ApiResource($manufactureInput);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function destroy(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $manufactureInput = ManufactureInput::findOrFail($id);

        $manufactureInput->isAllowedToDelete();

        $manufactureInput->requestCancel($request);

        DB::connection('tenant')->commit();

        return new ApiResource($manufactureInput);
    }
}
