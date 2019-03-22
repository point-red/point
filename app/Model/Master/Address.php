<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Address extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning addressable models.
     */
    public function addressable()
    {
        return $this->morphTo();
    }

    public static function saveFromRelation($obj, $addresses)
    {
        if ($addresses) {
            // Delete address
            $ids = array_column($addresses, 'id');
            Address::where('addressable_id', $obj->id)
                ->where('addressable_type', get_class($obj))
                ->whereNotIn('id', $ids)->delete();

            for ($i = 0; $i < count($addresses); $i++) {
                if ($addresses[$i]['address'] == null) {
                    break;
                }
                // If address has id then update existing address
                // If not then create new address
                if (isset($addresses[$i]['id'])) {
                    $address = Address::findOrFail($addresses[$i]['id']);
                } else {
                    $address = new Address;
                }

                $address->label = $addresses[$i]['label'] ?? null;
                $address->address = $addresses[$i]['address'];
                $address->city = $addresses[$i]['city'] ?? null;
                $address->state = $addresses[$i]['state'] ?? null;
                $address->country = $addresses[$i]['country'] ?? null;
                $address->zip_code = $addresses[$i]['zip_code'] ?? null;
                $address->latitude = $addresses[$i]['latitude'] ?? null;
                $address->longitude = $addresses[$i]['longitude'] ?? null;
                $address->addressable_type = get_class($obj);
                $address->addressable_id = $obj->id;
                $address->save();
            }
        }

    }
}
