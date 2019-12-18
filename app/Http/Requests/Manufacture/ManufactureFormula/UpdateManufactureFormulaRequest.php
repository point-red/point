<?php

namespace App\Http\Requests\Manufacture\ManufactureFormula;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManufactureFormulaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rulesForm = ValidationRule::form();

        $rulesManufacturFormula = [
            'manufacture_process_id' => ValidationRule::foreignKey('manufacture_processes'),
            'manufacture_process_name' => 'required|string',
            'name' => 'required|string',
            'raw_materials' => 'required|array',
            'finish_goods' => 'required|array',
        ];

        $rulesManufactureFormulaRawMaterials = [
            'raw_materials.*.item_id' => ValidationRule::foreignKey('items'),
            'raw_materials.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'raw_materials.*.item_name' => 'required|string',
            'raw_materials.*.warehouse_name' => 'required|string',
            'raw_materials.*.quantity' => ValidationRule::quantity(),
            'raw_materials.*.unit' => ValidationRule::unit(),
        ];

        $rulesManufactureFormulaFinishGoods = [
            'finish_goods.*.item_id' => ValidationRule::foreignKey('items'),
            'finish_goods.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'finish_goods.*.item_name' => 'required|string',
            'finish_goods.*.warehouse_name' => 'required|string',
            'finish_goods.*.quantity' => ValidationRule::quantity(),
            'finish_goods.*.unit' => ValidationRule::unit(),
        ];

        return array_merge($rulesForm, $rulesManufacturFormula, $rulesManufactureFormulaRawMaterials, $rulesManufactureFormulaFinishGoods);
    }
}
