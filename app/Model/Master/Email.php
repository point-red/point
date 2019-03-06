<?php

namespace App\Model\Master;

use App\Model\MasterModel;

class Email extends MasterModel
{
    protected $connection = 'tenant';

    /**
     * Get all of the owning emailable models.
     */
    public function emailable()
    {
        return $this->morphTo();
    }

    public static function saveFromRelation($obj, $emails)
    {
        if ($emails) {
            // Delete email
            $ids = array_column($emails, 'id');
            Email::where('emailable_id', $obj->id)
                ->where('emailable_type', get_class($obj))
                ->whereNotIn('id', $ids)->delete();

            for ($i = 0; $i < count($emails); $i++) {
                if ($emails[$i]['email'] == null) {
                    break;
                }
                // If email has id then update existing email
                // If not then create new email
                if (isset($emails[$i]['id'])) {
                    $email = Email::findOrFail($emails[$i]['id']);
                } else {
                    $email = new Email;
                }

                $email->label = $emails[$i]['label'] ?? null;
                $email->email = $emails[$i]['email'];
                $email->is_main = $emails[$i]['is_main'] ?? false;
                $email->emailable_type = get_class($obj);
                $email->emailable_id = $obj->id;
                $email->save();
            }
        }

    }
}
