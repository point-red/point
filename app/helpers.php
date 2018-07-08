<?php

if (! function_exists('log_array')) {
    /**
     * Get database size.
     *
     * @param        $databaseName
     * @param string $connection
     *
     * @return float
     */
    function log_object($object)
    {
        info(print_r($object, true));
    }
}
