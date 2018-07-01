<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiResult;

use Illuminate\Http\Request;
use App\Rules\NumberNotInRange;
use Illuminate\Validation\Rule;
use App\Model\HumanResource\Kpi\KpiResult;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKpiResultRequest extends FormRequest
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
        return [
            'score_min' => [
                'required',
                'numeric',
                'max:'.$this->score_max,
                Rule::unique('tenant.kpi_results')->ignore($this->id),
                new NumberNotInRange(KpiResult::class, 'score_min', 'score_max', $this->score_min, $this->score_max, $this->id),
            ],
            'score_max' => [
                'required',
                'numeric',
                'min:'.$this->score_min,
                Rule::unique('tenant.kpi_results')->ignore($this->id),
                new NumberNotInRange(KpiResult::class, 'score_min', 'score_max', $this->score_min, $this->score_max, $this->id),
            ],
            'criteria' => [
                'required',
                Rule::unique('tenant.kpi_results')->ignore($this->id),
            ],
            'notes' => ['required'],
        ];
    }
}
