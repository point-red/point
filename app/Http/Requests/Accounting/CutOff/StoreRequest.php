<?php

namespace App\Http\Requests\Accounting\CutOff;

use App\Rules\CutOffDetailRule;
use App\Rules\CutOffNonSubledgerRule;
use App\Rules\NotExistInJournal;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    const MIN_1 = 'numeric|min:1';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (env('APP_ENV') === 'testing') {
            return true;
        }

        return tenant(auth()->user()->id)->hasPermissionTo('create cut off');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(
            $this->paymentRules(),
            $this->itemRules(),
            $this->assetRules(),
            [
                'date' => 'required|date',
                'details.*' => new CutOffDetailRule(),
                'details.*.chart_of_account_id' => ['required', 'numeric'], // , new NotExistInJournal
                'details.*.debit' => 'required|numeric',
                'details.*.credit' => 'required|numeric',
                'details.*.items' => 'nullable'
            ],
        );
    }

    private function paymentRules() {
        $if = 'required_if:details.*.chart_of_account_sub_ledger,CUSTOMER,EMPLOYEE,EXPEDITION,SUPLIER';
        return [
            'details.*.items.*.object_id' => $if,
            'details.*.items.*.amount' => $if.'|'.self::MIN_1,
            'details.*.items.*.date' => $if.'|date'
        ];
    }

    private function itemRules() {
        $ifItem = 'required_if:details.*.chart_of_account_sub_ledger,ITEM';
        return [
            'details.*.items.*.object_id' => $ifItem,
            'details.*.items.*.warehouse_id' => $ifItem,
            'details.*.items.*.quantity' => $ifItem,
            'details.*.items.*.unit' => $ifItem,
            'details.*.items.*.converter' => $ifItem,
            'details.*.items.*.price' => $ifItem.'|'.self::MIN_1,
            'details.*.items.*.total' => $ifItem.'|'.self::MIN_1,
        ];
    }

    private function assetRules() {
        $ifAsset = 'required_if:details.*.chart_of_account_sub_ledger,FIXED ASSET';
        return [
            'details.*.items.*.object_id' => $ifAsset,
            'details.*.items.*.supplier_id' => $ifAsset,
            'details.*.items.*.location' => $ifAsset,
            'details.*.items.*.purchase_date' => $ifAsset.'|date',
            'details.*.items.*.quantity' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.price' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.total' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.accumulation' => $ifAsset.'|numeric',
            'details.*.items.*.book_value' => $ifAsset.'|'.self::MIN_1
        ];
    }
}
