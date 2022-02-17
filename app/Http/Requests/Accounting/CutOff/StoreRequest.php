<?php

namespace App\Http\Requests\Accounting\CutOff;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    const MIN_1 = 'numeric|min:1';
    const ITEM_AMOUNT = 'details.*.items.*.amount';
    const ITEM_DATE = 'details.*.items.*.date';
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
            [
                'date' => 'required|date',
                'details.*.chart_of_account_id' => 'required|numeric',
                'details.*.debit' => 'required|numeric',
                'details.*.credit' => 'required|numeric',
                // 'details.*.items' => 'nullable|required_unless:details.*.chart_of_account_sub_ledger,null'
            ],
            $this->customerRules(),
            $this->expeditionRules(),
            $this->employeeRules(),
            $this->supplierRules(),
            $this->itemRules(),
            $this->assetRules()
        );
    }

    private function customerRules() {
        $ifCustomer = 'required_if:details.*.chart_of_account_sub_ledger,CUSTOMER';
        return [
            self::ITEM_AMOUNT => $ifCustomer.'|'.self::MIN_1,
            self::ITEM_DATE => $ifCustomer.'|date'
        ];
    }

    private function expeditionRules() {
        $isSupplier = 'required_if:details.*.chart_of_account_sub_ledger,EXPEDITION';
        return [
            self::ITEM_AMOUNT => $isSupplier.'|'.self::MIN_1,
            self::ITEM_DATE => $isSupplier.'|date'
        ];
    }

    private function employeeRules() {
        $ifEmployee = 'required_if:details.*.chart_of_account_sub_ledger,EMPLOYEE';
        return [
            self::ITEM_AMOUNT => $ifEmployee.'|'.self::MIN_1,
            self::ITEM_DATE => $ifEmployee.'|date'
        ];
    }

    private function supplierRules() {
        $isSupplier = 'required_if:details.*.chart_of_account_sub_ledger,SUPPLIER';
        return [
            self::ITEM_AMOUNT => $isSupplier.'|numeric|min:1',
            self::ITEM_DATE => $isSupplier.'|date'
        ];
    }

    private function itemRules() {
        $ifItem = 'required_if:details.*.chart_of_account_sub_ledger,ITEM';
        return [
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
            'details.*.items.*.supplier_id' => $ifAsset,
            'details.*.items.*.location' => $ifAsset,
            'details.*.items.*.purchase_date' => $ifAsset.'|date',
            'details.*.items.*.quantity' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.price' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.total' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.accumulation' => $ifAsset.'|'.self::MIN_1,
            'details.*.items.*.book_value' => $ifAsset.'|'.self::MIN_1
        ];
    }
}
