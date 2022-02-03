<?php

namespace App\Imports\Master;

use App\Model\Master\Item;
use App\Model\Master\ItemGroup;
use App\Model\Accounting\ChartOfAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ItemImport implements ToCollection
{
  private $success = 0;
  private $fail = 0;
  private $start_row = 0;

  public function collection(Collection $rows)
  {
    $index = 0;

    foreach ($rows as $row) {
      $index++;
      if($index <= $this->start_row)  {
        continue;
      }
      if ($row[request()->get("name")] != null && $row[request()->get("code")] != null && $row[request()->get("chart_of_account")] != null) {
          //set item
          $item = $this->generateItem($row);    
          //check chart of account
          $accounts = ChartOfAccount::select('id')->where('alias', strtoupper($item['chart_of_account']))->first();
          if(isset($accounts->id)){
              $item['chart_of_account_id'] = $accounts->id;
                if(isset($item['group_name'])){
                    $itemGroup = ItemGroup::select('id','name')->where('name', $item['group_name'])->get();
                    if($itemGroup->isEmpty()){
                        $itemGroup = new ItemGroup();
                        $itemGroup->name = $item['group_name'];
                        $itemGroup->save();
                        $itemGroup = [$itemGroup];
                    }
                    $item['groups'] = $itemGroup;
                }
                $save = Item::create($item);
                $this->success++;
          }else{
              $this->fail++;
          }
      }else{
        $this->fail++; 
      }
    }
  }

  public function generateItem($row)
  {
    $item['code'] = $row[request()->get("code")];
    $item['name'] = $row[request()->get("name")];
    $item['chart_of_account'] = $row[request()->get("chart_of_account")];
    $item['units'] = [];
    if(request()->get("units_converter_1") != null && request()->get("units_measurement_1") != null){
        if($row[request()->get("units_converter_1")] != null && $row[request()->get("units_measurement_1")] != null){
            array_push($item['units'], $this->generateUnits($row[request()->get("units_converter_1")],$row[request()->get("units_measurement_1")]));
        }
    }
    if(request()->get("units_converter_2") != null && request()->get("units_measurement_2") != null){
      if($row[request()->get("units_converter_2")] != null && $row[request()->get("units_measurement_2")] != null){
          array_push($item['units'], $this->generateUnits($row[request()->get("units_converter_2")],$row[request()->get("units_measurement_2")]));
      }
    }
    if(empty($item['units'])){
      array_push($item['units'], $this->generateUnits('pcs', 1));
    }
    if(request()->get("require_expiry_date") != null && $row[request()->get("require_expiry_date")]!= null){
      $item['require_expiry_date'] = $row[request()->get("require_expiry_date")];
    }
    if(request()->get("require_production_number") != null && $row[request()->get("require_production_number")]!= null){
      $item['require_production_number'] = $row[request()->get("require_production_number")];
    }
    if(request()->get("group_name") != null && $row[request()->get("group_name")]!= null){
      $item['group_name'] = $row[request()->get("group_name")];
    }
    return $item;
  }

  public function generateUnits($converter, $measurement)
  {
    return [
        "label" => $converter,
        "name" => $converter,
        "converter" => $measurement,
        "default_purchase" => false,
        "default_sales" => false
    ];
  }

  public function startRow($start_row)
  {
      $this->start_row = $start_row;
  }

  public function getResult()
  {
      return ["success" => $this->success, "fail" => $this->fail];
  }

}
