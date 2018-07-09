<?php

if (! function_exists('log_array')) {
    /**
     * Log an object / array
     *
     * @param $object
     */
    function log_object($object)
    {
        info(print_r($object, true));
    }
}

if (! function_exists('tenant')) {
    /**
     * Get Tenant User
     *
     * @param $userId
     *
     * @return \App\Model\Master\User
     */
    function tenant($userId = null)
    {
        return \App\Model\Master\User::findOrFail($userId ?? auth()->user()->id);
    }
}
