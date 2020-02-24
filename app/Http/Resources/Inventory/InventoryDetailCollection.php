<?php

namespace App\Http\Resources\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryDetailCollection extends ResourceCollection
{
    protected $dateFrom;
    protected $dateTo;
    protected $limit;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $openingBalance = $this->getOpeningBalance($request->route('itemId'), $request->warehouse_id, $request->page);
        $stockIn = $this->getTotalStockIn($request->route('itemId'), $request->warehouse_id, $request->page);
        $stockOut = $this->getTotalStockOut($request->route('itemId'), $request->warehouse_id, $request->page);

        return [
            'opening_balance' => $openingBalance['opening_balance'],
            'opening_balance_current_page' => $openingBalance['opening_balance_current_page'],
            'ending_balance' => $this->getEndingBalance($request->route('itemId'), $request->warehouse_id),
            'stock_in' => $stockIn['total'],
            'stock_out' => $stockOut['total'],
            'data' => $this->collection
        ];
    }

    public function dateFrom($value)
    {
        $this->dateFrom = convert_to_server_timezone($value);
    }

    public function dateTo($value)
    {
        $this->dateTo = convert_to_server_timezone($value);
    }

    public function limit($value)
    {
        $this->limit = $value;
    }

    private function getOpeningBalance($itemId, $warehouseId, $page)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $openingBalance = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('forms.date', '<', $this->dateFrom)
            ->sum('quantity');

        $previousBalance = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->take(($page - 1) * $this->limit)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return [
            'opening_balance' => (double) $openingBalance,
            'opening_balance_current_page' => (double) $openingBalance + $previousBalance
        ];
    }

    private function getTotalStockIn($itemId, $warehouseId, $page)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $total = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '>', 0)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return ['total' => (double) $total];
    }

    private function getTotalStockOut($itemId, $warehouseId, $page)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $total = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '<', 0)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return ['total' => (double) $total];
    }

    private function getEndingBalance($itemId, $warehouseId)
    {
        $query = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId);

        if (!$this->dateTo) {
            return (double) $query->sum('quantity');
        }

        return (double) $query->where('forms.date', '<=', $this->dateTo)->sum('quantity');
    }
}
