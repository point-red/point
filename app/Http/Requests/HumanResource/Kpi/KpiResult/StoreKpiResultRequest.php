<?php

namespace App\Http\Requests\HumanResource\Kpi\KpiResult;

use App\Model\HumanResource\Kpi\KpiResult;
use App\Rules\NumberNotInRange;
use Illuminate\Foundation\Http\FormRequest;

class StoreKpiResultRequest extends FormRequest
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
                'unique:tenant.kpi_results,score_min',
                new NumberNotInRange(KpiResult::class, 'score_min', 'score_max', $this->score_min, $this->score_max),
            ],
            'score_max' => [
                'required',
                'numeric',
                'min:'.$this->score_min,
                'unique:tenant.kpi_results,score_max',
                new NumberNotInRange(KpiResult::class, 'score_min', 'score_max', $this->score_min, $this->score_max),
            ],
            'criteria' => [
                'required',
                'unique:tenant.kpi_results,criteria',
            ],
            'notes' => ['required'],
        ];
    }
}
