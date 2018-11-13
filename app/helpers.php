<?php

if (! function_exists('log_array')) {
    /**
     * Log an object / array.
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
     * Get Tenant User.
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

if (! function_exists('get_invitation_code')) {
    /**
     * Get Invitation Code.
     */
    function get_invitation_code()
    {
        $activationCode = null;

        do {
            $random = strtoupper(str_random(12));
            if (! \App\Model\Project\Project::where('invitation_code', $random)->first()) {
                $activationCode = $random;
            }
        } while ($activationCode == null);

        return $activationCode;
    }

    if (! function_exists('capitalize')) {
        /**
         * Capitalize string.
         *
         * @param $string
         * @return string
         */
        function capitalize($string)
        {
            return ucfirst(strtolower($string));
        }
    }

    if (! function_exists('pagination')) {
        /**
         * Paginate collection.
         *
         * @param $query
         * @param null $limit
         * @return string
         */
        function pagination($query, $limit = null)
        {
            if (!$limit) {
                return $query->paginate(1000);
            }

            return $query->paginate($limit);
        }
    }

    if (! function_exists('date_from')) {
        /**
         * Convert hour:minute:second into 00:00:00
         *
         * @param $date
         * @param bool $first
         * @return string
         */
        function date_from($date, $first = false)
        {
            if ($first) {
                return date('Y-m-01 00:00:00', strtotime($date));
            }

            return date('Y-m-d 00:00:00', strtotime($date));
        }
    }

    if (! function_exists('date_to')) {
        /**
         * Convert hour:minute:second into 00:00:00
         *
         * @param $date
         * @param bool $last
         * @return string
         */
        function date_to($date, $last = false)
        {
            if ($last) {
                return date('Y-m-t 23:59:59', strtotime($date));
            }

            return date('Y-m-d 23:59:59', strtotime($date));
        }
    }
}
