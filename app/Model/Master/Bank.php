<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Bank extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning bankable models.
     */
    public function bankable()
    {
        return $this->morphTo();
    }

    public static function saveFromRelation($obj, $banks)
    {
        if ($banks) {
            // Delete bank
            $ids = array_column($banks, 'id');
            self::where('bankable_id', $obj->id)
                ->where('bankable_type', get_class($obj))
                ->whereNotIn('id', $ids)->delete();

            for ($i = 0; $i < count($banks); $i++) {
                // If bank has id then update existing bank
                // If not then create new bank
                if (isset($banks[$i]['id'])) {
                    $bank = self::findOrFail($banks[$i]['id']);
                } else {
                    $bank = new self;
                }

                $bank->name = $banks[$i]['name'];
                $bank->branch = $banks[$i]['branch'] ?? null;
                $bank->account_number = $banks[$i]['account_number'];
                $bank->account_name = $banks[$i]['account_name'];
                $bank->notes = $banks[$i]['notes'] ?? null;
                $bank->bankable_type = get_class($obj);
                $bank->bankable_id = $obj->id;
                $bank->save();
            }
        }
    }
}
