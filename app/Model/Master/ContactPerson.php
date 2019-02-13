<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class ContactPerson extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning contactable models.
     */
    public function contactable()
    {
        return $this->morphTo();
    }

    public static function saveFromRelation($obj, $contactPersons)
    {
        if ($contactPersons) {
            // Delete contact
            $ids = array_column($contactPersons, 'id');
            ContactPerson::where('contactable_id', $obj->id)
                ->where('contactable_type', get_class($obj))
                ->whereNotIn('id', $ids)->delete();

            for ($i = 0; $i < count($contactPersons); $i++) {
                // If contact has id then update existing contact
                // If not then create new contact
                if (isset($contactPersons[$i]['id'])) {
                    $contactPerson = ContactPerson::findOrFail($contactPersons[$i]['id']);
                } else {
                    $contactPerson = new ContactPerson;
                }

                $contactPerson->code = $contactPersons[$i]['code'] ?? null;
                $contactPerson->department = $contactPersons[$i]['department'] ?? null;
                $contactPerson->title = $contactPersons[$i]['title'] ?? null;
                $contactPerson->name = $contactPersons[$i]['name'];
                $contactPerson->phone = $contactPersons[$i]['phone'] ?? null;
                $contactPerson->email = $contactPersons[$i]['email'] ?? null;
                $contactPerson->contactable_type = get_class($obj);
                $contactPerson->contactable_id = $obj->id;
                $contactPerson->save();
            }
        }

    }
}
