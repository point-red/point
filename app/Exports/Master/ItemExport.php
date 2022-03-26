<?php

namespace App\Exports\Master;

use App\Model\Master\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;

class ItemExport implements FromView
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        /** @var Collection<Item> */
        $items = Item::query()
            ->from(Item::getTableName().' as '.Item::$alias)
            ->eloquentFilter($this->request)
            ->get();

        $highestTotalItemUnit = 0;
        foreach ($items as $item) {
            $totalUnit = $item->units->count();
            if ($totalUnit > $highestTotalItemUnit) {
                $highestTotalItemUnit = $totalUnit;
            }
        }

        return view('exports.master.items', [
            'highestTotalItemUnit' => $highestTotalItemUnit,
            'items' => $items,
        ]);
    }
}
