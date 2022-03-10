<?php 
namespace App\Exports;

use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Master\ItemGroup;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ItemsExport implements FromView,ShouldAutoSize
{
    public function view(): View
    {
        $items = Item::select('a.id','a.code','a.name','a.require_expiry_date','a.require_production_number',
		'b.alias as account_alias','c.name as def_purchase_alias','d.name as def_sales_alias','f.name as group_name')
		->from(Item::getTableName().' as a')
		->leftJoin('chart_of_accounts as b', 'a.chart_of_account_id', '=', 'b.id')
		->leftJoin('item_units as c', 'a.unit_default_purchase', '=', 'c.id')
		->leftJoin('item_units as d', 'a.unit_default_sales', '=', 'd.id')
		->leftJoin('item_item_group as e', 'a.id', '=', 'e.item_id')
		->leftJoin('item_groups as f', 'e.item_group_id', '=', 'f.id')->get();
		
		$countitem=count($items);
		for($i=0;$i<$countitem;$i++) { 
		 $items[$i]->listsatuan=ItemUnit::select('name','converter')->from('item_units')
		 ->where('disabled',0)->where('item_id',$items[$i]->id)->orderBy('id','asc')->get(); }
		
		return view('exports.items', ['items' => $items ]);
    }
}
?>