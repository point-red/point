<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Phone extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning phoneable models.
     */
    public function phoneable()
    {
        return $this->morphTo();
    }

    public static function saveFromRelation($obj, $phones)
    {
        if ($phones) {
            // Delete phone
            $ids = array_column($phones, 'id');
            Phone::where('phoneable_id', $obj->id)
                ->where('phoneable_type', get_class($obj))
                ->whereNotIn('id', $ids)->delete();

            for ($i = 0; $i < count($phones); $i++) {
                if ($phones[$i]['number'] == null) {
                    break;
                }
                // If phone has id then update existing phone
                // If not then create new phone
                if (isset($phones[$i]['id'])) {
                    $phone = Phone::findOrFail($phones[$i]['id']);
                } else {
                    $phone = new Phone;
                }

                $phone->label = $phones[$i]['label'] ?? null;
                $phone->country_code = $phones[$i]['country_code'] ?? null;
                $phone->number = $phones[$i]['number'];
                $phone->is_main = $phones[$i]['is_main'] ?? false;
                $phone->phoneable_type = get_class($obj);
                $phone->phoneable_id = $obj->id;
                $phone->save();
            }
        }

    }
}
