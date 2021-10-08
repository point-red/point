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
        $customer->name = $row[1];
        $customer->address = $row[2];
        $customer->phone = $row[3];
        $customer->branch_id = 1;
        $customer->save();
      }
    }
}
