<?php

namespace App\Http\Requests\Manufacture\ManufactureInput;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManufactureInputRequest extends FormRequest
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

        $rulesManufacturInput = [
            'manufacture_machine_id' => ValidationRule::foreignKey('manufacture_machines'),
            'manufacture_process_id' => ValidationRule::foreignKey('manufacture_processes'),
            'manufacture_machine_name' => 'required|string',
            'manufacture_process_name' => 'required|string',
            'raw_materials' => 'required|array',
            'finished_goods' => 'required|array',
            'request_approval_to' => 'required',
        ];

        $rulesManufactureInputRawMaterials = [
            'raw_materials.*.item_id' => ValidationRule::foreignKey('items'),
            'raw_materials.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'raw_materials.*.item_name' => 'required|string',
            'raw_materials.*.warehouse_name' => 'required|string',
            'raw_materials.*.quantity' => ValidationRule::quantity(),
            'raw_materials.*.unit' => ValidationRule::unit(),
        ];

        $rulesManufactureInputFinishedGoods = [
            'finished_goods.*.item_id' => ValidationRule::foreignKey('items'),
            'finished_goods.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'finished_goods.*.item_name' => 'required|string',
            'finished_goods.*.warehouse_name' => 'required|string',
            'finished_goods.*.quantity' => ValidationRule::quantity(),
            'finished_goods.*.unit' => ValidationRule::unit(),
        ];

        return array_merge($rulesForm, $rulesManufacturInput, $rulesManufactureInputRawMaterials, $rulesManufactureInputFinishedGoods);
    }
}
