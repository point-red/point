<?php

namespace App\Exports\PinPoint;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Item;
use App\Model\Master\ItemGroup;
use App\Model\Master\ItemUnit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemExport implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
	public function __construct($dateFrom = null, $dateTo = null)
	{
		$this->dateFrom = $dateFrom;
		$this->dateTo = $dateTo;
	}

	public function query()
	{
		$items = Item::query()
			->leftJoin(ChartOfAccount::getTableName(), ChartOfAccount::getTableName() . '.id', '=', Item::getTableName() . '.chart_of_account_id')
			->leftJoin('item_item_group as ig', 'ig.item_id', '=', Item::getTableName() . '.id')
			->leftJoin(ItemGroup::getTableName(), ItemGroup::getTableName() . '.id', '=', 'ig.item_group_id')
			->select(Item::getTableName() . '.id as item_id')
			->addSelect(Item::getTableName() . '.code as item_code')
			->addSelect(Item::getTableName() . '.name as item_name')
			->addSelect(Item::getTableName() . '.require_production_number as item_require_production_number')
			->addSelect(Item::getTableName() . '.require_expiry_date as item_require_expiry_date')
			->addSelect(Item::getTableName() . '.unit_default_purchase as item_unit_default_purchase')
			->addSelect(Item::getTableName() . '.unit_default_sales as item_unit_default_sales')
			->addSelect(ChartOfAccount::getTableName() . '.name as chart_account_name')
			->addSelect(ItemGroup::getTableName() . '.name as group_name');
		if ($this->dateFrom && $this->dateTo) {
			$items->whereBetween(ItemUnit::getTableName() . '.created_at', [$this->dateFrom, $this->dateTo]);
		}

		return $items;
	}

	public function headings(): array
	{
		return [
			'Item Code',
			'Item Name',
			'Chart of Account ',
			'Unit Of Converter 1',
			'Converter',
			'Unit Of Converter 2',
			'Converter',
			'Unit Of Converter 2',
			'Converter',
			'Expiry Date',
			'Production Number',
			'Default Purchase',
			'Default Sales',
			'Group',
		];
	}

	public function map($row): array
	{
		$units = $this->_getItemUnitsConverter($row->item_id);

		return [
			$row->item_code,
			$row->item_name,
			$row->chart_account_name,
			count($units) > 0 ? $units[0]['converter'] : '',
			count($units) > 0 ? $units[0]['label'] : '',
			count($units) > 1 ? $units[1]['converter'] : '',
			count($units) > 1 ? $units[1]['label'] : '',
			count($units) > 2 ? $units[2]['converter'] : '',
			count($units) > 2 ? $units[2]['label'] : '',
			$row->item_require_expiry_date === 1 ? 'true' : 'false',
			$row->item_require_production_number === 1 ? 'true' : 'false',
			$row->item_unit_default_purchase,
			$row->item_unit_default_sales,
			$row->group_name,
		];
	}

	public function title(): string
	{
		return 'Items';
	}

	private function _getItemUnitsConverter($itemId)
	{
		return ItemUnit::where('item_id', '=', $itemId)
			->select('label', 'converter')
			->get();
	}
}
