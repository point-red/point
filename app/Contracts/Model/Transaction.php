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

    public function updateStatus();

    public function updateReference();
}
