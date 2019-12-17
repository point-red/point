<?php

namespace App\Http\Requests\Manufacture\ManufactureOutput;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreManufactureOutputRequest extends FormRequest
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

        $rulesManufacturOutput = [
            'manufacture_machine_id' => ValidationRule::foreignKey('manufacture_machines'),
            'manufacture_input_id' => ValidationRule::foreignKey('manufacture_inputs'),
            'manufacture_machine_name' => 'required|string',
            'finish_goods' => 'required|array',
        ];

        $rulesManufactureOutputFinishGoods = [
            'finish_goods.*.item_id' => ValidationRule::foreignKey('items'),
            'finish_goods.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'finish_goods.*.input_finish_good_id' => ValidationRule::foreignKey('manufacture_input_finish_goods'),            
            'finish_goods.*.item_name' => 'required|string',
            'finish_goods.*.warehouse_name' => 'required|string',
            'finish_goods.*.item_name' => 'required|string',
            'finish_goods.*.warehouse_name' => 'required|string',
            'finish_goods.*.quantity' => ValidationRule::quantity(),
            'finish_goods.*.unit' => ValidationRule::unit(),
        ];

        return array_merge($rulesForm, $rulesManufacturOutput, $rulesManufactureOutputFinishGoods);
    }
}
