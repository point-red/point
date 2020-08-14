<?php

namespace App\Http\Requests\Manufacture\ManufactureOutput;

use App\Http\Requests\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManufactureOutputRequest extends FormRequest
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
            'manufacture_process_id' => ValidationRule::foreignKey('manufacture_processes'),
            'manufacture_input_id' => ValidationRule::foreignKey('manufacture_inputs'),
            'manufacture_machine_name' => 'required|string',
            'manufacture_process_name' => 'required|string',
            'finished_goods' => 'required|array',
        ];

        $rulesManufactureOutputFinishedGoods = [
            'finished_goods.*.item_id' => ValidationRule::foreignKey('items'),
            'finished_goods.*.warehouse_id' => ValidationRule::foreignKey('warehouses'),
            'finished_goods.*.input_finish_good_id' => ValidationRule::foreignKey('manufacture_input_finished_goods'),
            'finished_goods.*.item_name' => 'required|string',
            'finished_goods.*.warehouse_name' => 'required|string',
            'finished_goods.*.item_name' => 'required|string',
            'finished_goods.*.warehouse_name' => 'required|string',
            'finished_goods.*.quantity' => ValidationRule::quantity(),
            'finished_goods.*.unit' => ValidationRule::unit(),
        ];

        return array_merge($rulesForm, $rulesManufacturOutput, $rulesManufactureOutputFinishedGoods);
    }
}
