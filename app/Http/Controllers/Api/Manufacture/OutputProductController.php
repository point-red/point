<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manufacture\ManufactureOutput\StoreManufactureOutputRequest;
use App\Http\Requests\Manufacture\ManufactureOutput\UpdateManufactureOutputRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Manufacture\ManufactureOutput\ManufactureOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OutputProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $outputs = ManufactureOutput::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('form', $fields)) {
                $outputs = $outputs->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', ManufactureOutput::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), ManufactureOutput::$morphName);
                });
            }
        }

        $outputs = pagination($outputs, $request->get('limit'));

        return new ApiCollection($outputs);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - manufacture_machine_id (Int)
     *  - manufacture_input_id (Int)
     *  - manufacture_machine_name (String)
     *  -
     *  - finish_goods (Array) :
     *      - manufacture_input_finish_good_id (Int)
     *      - item_name (String)
     *      - warehouse_name (String)
     *      - quantity (Decimal)
     *      - unit (String).
     *
     * @param StoreManufactureOutputRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreManufactureOutputRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $manufactureOutput = ManufactureOutput::create($request->all());
            $manufactureOutput
                ->load('form')
                ->load('manufactureMachine')
                ->load('manufactureInput')
                ->load('finishGoods.manufactureInputFinishGood');

            return new ApiResource($manufactureOutput);
        });
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
        $manufactureOutput = ManufactureOutput::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($manufactureOutput);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateManufactureOutputRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateManufactureOutputRequest $request, $id)
    {
        $manufactureOutput = ManufactureOutput::findOrFail($id);
        $manufactureOutput->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $manufactureOutput) {
            $manufactureOutput->form->archive();
            $request['number'] = $manufactureOutput->form->edited_number;
            $request['old_increment'] = $manufactureOutput->form->increment;

            $manufactureOutput = ManufactureOutput::create($request->all());
            $manufactureOutput
                ->load('form')
                ->load('manufactureMachine')
                ->load('manufactureInput')
                ->load('finishGoods.manufactureInputFinishGood');

            return new ApiResource($manufactureOutput);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $manufactureOutput = ManufactureOutput::findOrFail($id);
        $manufactureOutput->isAllowedToDelete();

        $response = $manufactureOutput->requestCancel($request);

        return response()->json([], 204);
    }
}
