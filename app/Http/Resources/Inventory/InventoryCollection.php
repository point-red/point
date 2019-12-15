<?php

namespace App\Http\Resources\Inventory;

use App\Model\Form;
use App\Model\Inventory\Inventory;
use App\Model\Master\Item;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryCollection extends ResourceCollection
{
    protected $dateFrom;
    protected $dateTo;
    protected $currentPage;
    protected $limit;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $openingBalance = $this->getOpeningBalance($request->item_id);
        $stockIn = $this->getTotalStockIn($request->item_id);
        $stockOut = $this->getTotalStockOut($request->item_id);

        return [
            'opening_balance' => $openingBalance['opening_balance'],
            'opening_balance_current_page' => $openingBalance['opening_balance_current_page'],
            'ending_balance' => $this->getEndingBalance($request->item_id),
            'stock_in' => $stockIn['total'],
            'stock_in_current_page' => $stockIn['total_current_page'],
            'stock_out' => $stockOut['total'],
            'stock_out_current_page' => $stockOut['total_current_page'],
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

    public function currentPage($value)
    {
        $this->currentPage = $value;
    }

    public function limit($value)
    {
        $this->limit = $value;
    }

    private function getOpeningBalance($itemId)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $openingBalance = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '<', $this->dateFrom)
            ->sum('quantity');

        $previousBalance = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->take(($this->currentPage - 1) * $this->limit)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return [
            'opening_balance' => (double) $openingBalance,
            'opening_balance_current_page' => (double) $openingBalance + $previousBalance
        ];
    }

    private function getTotalStockIn($itemId)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $in = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '>', 0)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        $inCurrentPage = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '>', 0)
            ->take(($this->currentPage - 1) * $this->limit)
            ->limit($this->limit)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return [
            'total' => (double) $in,
            'total_current_page' => (double) $inCurrentPage
        ];
    }

    private function getTotalStockOut($itemId)
    {
        if (!$this->dateFrom) {
            return 0;
        }

        $out = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '<', 0)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        $outCurrentPage = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId)
            ->where('forms.date', '>=', $this->dateFrom)
            ->where('forms.date', '<=', $this->dateTo)
            ->where('quantity', '<', 0)
            ->take(($this->currentPage - 1) * $this->limit)
            ->limit($this->limit)
            ->orderBy('forms.date', 'asc')
            ->get()
            ->sum('quantity');

        return [
            'total' => (double) $out,
            'total_current_page' => (double) $outCurrentPage
        ];
    }

    private function getEndingBalance($itemId)
    {
        $query = Inventory::join(Form::getTableName(), Form::getTableName('id'), '=', Inventory::getTableName('form_id'))
            ->where('item_id', $itemId);

        if (!$this->dateTo) {
            return (double) $query->sum('quantity');
        }

        return (double) $query->where('forms.date', '<=', $this->dateTo)->sum('quantity');
    }
}
