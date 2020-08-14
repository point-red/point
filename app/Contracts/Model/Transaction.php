<?php

namespace App\Contracts\Model;

interface Transaction
{
    /**
     * Handle to create model
     *
     * @param $data
     * @return void
     */
    public static function create($data);

    public function isAllowedToUpdate();
    
    public function isAllowedToDelete();

    // update reference status
    // ex: cancelling purchase order should make purchase request pending again
    public function updateReference();

    // updating own status
    // when some reference feature trigger this updateStatus() method,
    // then this will checking all reference to update this status is done or not
    public function updateStatus();
}
