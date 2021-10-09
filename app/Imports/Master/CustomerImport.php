<?php

namespace App\Imports\Master;

use App\Model\Master\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CustomerImport implements ToCollection
{
    public function collection(Collection $rows)
    {
      $index = 0;
      foreach ($rows as $row) {
        $index++;
        if($index == 1)  {
          continue;
        }
        
        $customer = new Customer();
        $customer->code = request()->get("code");
        $customer->name = request()->get("name");
        $customer->address = request()->get("address");
        $customer->phone = request()->get("phone");
        $customer->email = request()->get("email");
        $customer->branch_id = auth()->user()->branch_id ?? 1;
        $customer->save();
      }
    }
}
