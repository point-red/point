<?php

namespace App\Imports\Master;

use App\Model\Master\Supplier;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;

class SupplierImport implements ToModel, SkipsOnError, SkipsOnFailure, WithValidation, WithStartRow
{
    private $success = 0;
    private $fail = 0;

    use Importable,  SkipsErrors, SkipsFailures;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $item = $this->generateItem($row);
        $this->success += 1;
        return new Supplier($item);
    }

    // public function collection(Collection $rows)
    // {
    //     foreach ($rows as $row) {
    //         $item = $this->generateItem($row); 
    //         if (Supplier::where('code', $item['code'])->first()) {
    //             $this->fail += 1;
    //             continue;
    //         }
    //         if (Supplier::create($item)) {
    //             $this->success += 1;
    //         }else {
    //             $this->fail += 1;
    //         }
    //     }
    // }

    public function startRow(): int
    {
        return request()->get("start_row");
    }

    public function generateItem($row)
    {
        $item['code'] = $row[request()->get("code")];
        $item['name'] = $row[request()->get("name")];
        $item['email'] = $row[request()->get("email")];
        $item['address'] = $row[request()->get("address")];
        $item['phone'] = $row[request()->get("phone")];
        $item['bank_branch'] = $row[request()->get("bank_branch")];
        $item['bank_name'] = $row[request()->get("bank_name")];
        $item['bank_account_name'] = $row[request()->get("bank_account_name")];
        $item['bank_account_number'] = $row[request()->get("bank_account_number")];
        return $item;
    }

    public function rules(): array
    {
        return [
            '*.' .request()->get("code") => 'unique:tenant.suppliers,code',
            request()->get("code") => 'unique:tenant.suppliers,code',
            '*.' .request()->get("email") => 'required|email',
            request()->get("email") => 'required|email',
            // 'name' => ['required'],
            // 'email' => ['email'],
            // 'bank_branch' => ['required'],
            // 'bank_name' => ['required'],
            // 'bank_account_name' => ['required'],
            // 'bank_account_number' => ['required'],
        ];
    }

    public function getResult()
    {
        return $this->success;
    }
}
