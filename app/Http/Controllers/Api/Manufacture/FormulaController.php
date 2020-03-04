<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manufacture\ManufactureFormula\StoreManufactureFormulaRequest;
use App\Http\Requests\Manufacture\ManufactureFormula\UpdateManufactureFormulaRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Manufacture\ManufactureFormula\ManufactureFormula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FormulaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $formulas = ManufactureFormula::eloquentFilter($request);

        if ($request->get('join')) {
            $fields = explode(',', $request->get('join'));

            if (in_array('form', $fields)) {
                $formulas = $formulas->join(Form::getTableName(), function ($q) {
                    $q->on(Form::getTableName('formable_id'), '=', ManufactureFormula::getTableName('id'))
                        ->where(Form::getTableName('formable_type'), ManufactureFormula::$morphName);
                });
            }
        }

        $formulas = pagination($formulas, $request->get('limit'));

        return new ApiCollection($formulas);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - manufacture_process_id (Int)
     *  - manufacture_process_name (String)
     *  - name (String)
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
     * @param StoreManufactureFormulaRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreManufactureFormulaRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $manufactureFormula = ManufactureFormula::create($request->all());
            $manufactureFormula
                ->load('form')
                ->load('manufactureProcess')
                ->load('rawMaterials.item')
                ->load('finishedGoods.item');

            return new ApiResource($manufactureFormula);
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
        $manufactureFormula = ManufactureFormula::eloquentFilter($request)->findOrFail($id);

        if ($request->has('with_archives')) {
            $manufactureFormula->archives = $manufactureFormula->archives();
        }

        if ($request->has('with_origin')) {
            $manufactureFormula->origin = $manufactureFormula->origin();
        }

        return new ApiResource($manufactureFormula);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateManufactureFormulaRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateManufactureFormulaRequest $request, $id)
    {
        $manufactureFormula = ManufactureFormula::findOrFail($id);
        $manufactureFormula->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $manufactureFormula) {
            $manufactureFormula->form->archive();
            $request['number'] = $manufactureFormula->form->edited_number;
            $request['old_increment'] = $manufactureFormula->form->increment;

            $manufactureFormula = ManufactureFormula::create($request->all());
            $manufactureFormula
                ->load('form')
                ->load('manufactureProcess')
                ->load('rawMaterials.item')
                ->load('finishedGoods.item');

            return new ApiResource($manufactureFormula);
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

        $formula = ManufactureFormula::findOrFail($id);

        $formula->isAllowedToDelete();

        $formula->requestCancel($request);

        DB::connection('tenant')->commit();

        return new ApiResource($formula);
    }
}
