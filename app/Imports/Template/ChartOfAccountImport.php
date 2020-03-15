<?php

namespace App\Imports\Template;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountGroup;
use App\Model\Accounting\ChartOfAccountType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ChartOfAccountImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $type = ChartOfAccountType::where('name', $row['type'])->first();
            if (!$type) {
                $type = new ChartOfAccountType;
                $type->name = strtoupper($row['type']);
                $type->alias = strtoupper($row['type_alias']);
                $type->save();
            }

            $group = ChartOfAccountGroup::where('name', $row['group'])->first();
            if ($row['group'] && !$group) {
                $group = new ChartOfAccountGroup();
                $group->name = strtoupper($row['group']);
                $group->alias = strtoupper($row['group_alias']);
                $group->save();
            }

            $account = ChartOfAccount::where('name', $row['name'])->first();
            if (!$account) {
                $account = new ChartOfAccount;
                $account->type_id = $type->id;
                if ($group) {
                    $account->group_id = $group->id;
                }
                $account->position = strtoupper($row['position']);
                $account->cash_flow = strtoupper($row['cash_flow']);
                $account->cash_flow_position = strtoupper($row['cash_flow_position']);
                $account->number = strtoupper($row['number']);
                $account->name = strtoupper($row['name']);
                $account->alias = strtoupper($row['alias']);
                $account->is_sub_ledger = strtoupper($row['is_sub_ledger']);
                $account->sub_ledger = strtoupper($row['sub_ledger']);
                $account->is_locked = strtoupper($row['is_locked']);
                $account->save();
            }
        }
    }
}
