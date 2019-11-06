<?php

use App\Model\SettingJournal;
use Carbon\Carbon;
use Illuminate\Support\Str;

if (! function_exists('log_object')) {
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
            $random = strtoupper(Str::random(12));
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
            if (! $limit) {
                return $query->paginate(1000);
            }

            return $query->paginate($limit);
        }
    }

    if (! function_exists('date_tz')) {
        /**
         * Convert hour:minute:second into 00:00:00.
         *
         * @param $datetime
         * @param null $fromTz
         * @param null $toTz
         * @return string
         * @throws Exception
         */
        function date_tz($datetime, $fromTz = null, $toTz = null)
        {
            if ($fromTz == null) {
                $fromTz = config()->get('project.timezone');
            }

            if ($toTz == null) {
                $toTz = config()->get('app.timezone');
            }

            $date = new DateTime($datetime, new DateTimeZone($fromTz));
            $date->setTimezone(new DateTimeZone($toTz));

            return $date->format('Y-m-d H:i:s');
        }
    }

    if (! function_exists('date_from')) {
        /**
         * Convert hour:minute:second into 00:00:00.
         *
         * @param $datetime
         * @param bool $firstDateOfMonth
         * @param bool $convertTime
         * @param null $fromTz
         * @param null $toTz
         * @return string
         * @throws Exception
         */
        function date_from($datetime, $firstDateOfMonth = false, $convertTime = false, $fromTz = null, $toTz = null)
        {
            if ($firstDateOfMonth) {
                $datetime = date('Y-m-01 H:i:s', strtotime($datetime));
            }

            if ($convertTime) {
                $datetime = date('Y-m-d 00:00:00', strtotime($datetime));
            }

            if ($fromTz == null) {
                $fromTz = config()->get('project.timezone');
            }

            if ($toTz == null) {
                $toTz = config()->get('app.timezone');
            }

            $date = new DateTime($datetime, new DateTimeZone($fromTz));
            $date->setTimezone(new DateTimeZone($toTz));

            return $date->format('Y-m-d H:i:s');
        }
    }

    if (! function_exists('date_to')) {
        /**
         * Convert hour:minute:second into 00:00:00.
         *
         * @param $datetime
         * @param bool $lastDateOfMonth
         * @param bool $convertTime
         * @param null $fromTz
         * @param null $toTz
         * @return string
         * @throws Exception
         */
        function date_to($datetime, $lastDateOfMonth = false, $convertTime = false, $fromTz = null, $toTz = null)
        {
            if ($lastDateOfMonth) {
                $datetime = date('Y-m-t H:i:s', strtotime($datetime));
            }

            if ($convertTime) {
                $datetime = date('Y-m-d 23:59:59', strtotime($datetime));
            }

            if ($fromTz == null) {
                $fromTz = config()->get('project.timezone');
            }

            if ($toTz == null) {
                $toTz = config()->get('app.timezone');
            }

            $date = new DateTime($datetime, new DateTimeZone($fromTz));
            $date->setTimezone(new DateTimeZone($toTz));

            return $date->format('Y-m-d H:i:s');
        }
    }

    if (! function_exists('convert_to_local_timezone')) {
        /**
         * Convert datetime to local timezone.
         *
         * @param $value
         * @param null $fromTz
         * @param null $toTz
         * @return string
         */
        function convert_to_local_timezone($value, $fromTz = null, $toTz = null)
        {
            if ($fromTz == null) {
                $fromTz = config()->get('app.timezone');
            }

            if ($toTz == null) {
                $toTz = config()->get('project.timezone');
            }

            return Carbon::parse($value, $fromTz)->timezone($toTz)->toDateTimeString();
        }
    }

    if (! function_exists('convert_to_server_timezone')) {
        /**
         * Convert datetime to server timezone.
         *
         * @param $value
         * @param null $fromTz
         * @param null $toTz
         * @return string
         */
        function convert_to_server_timezone($value, $fromTz = null, $toTz = null)
        {
            if ($fromTz == null) {
                $fromTz = config()->get('project.timezone');
            }

            if ($toTz == null) {
                $toTz = config()->get('app.timezone');
            }

            return Carbon::parse($value, $fromTz)->timezone($toTz)->toDateTimeString();
        }
    }

    if (! function_exists('get_if_set')) {
        /**
         * Convert datetime to server timezone.
         *
         * @param $var
         * @return string
         */
        function get_if_set(&$var)
        {
            if (isset($var)) {
                return $var;
            }
        }
    }

    if (! function_exists('get_tenant_db_name')) {
        /**
         * Get database name for this tenant.
         *
         * @param $tenantCode
         * @return string
         */
        function get_tenant_db_name($tenantCode)
        {
            $project = \App\Model\Project\Project::where('code', $tenantCode)->first();

            return env('DB_DATABASE').'_'.strtolower($project->code);
        }
    }

    if (! function_exists('get_setting_journal')) {
        /**
         * Get default journal account for transaction.
         *
         * @param $feature
         * @param $name
         * @return string
         */
        function get_setting_journal($feature, $name)
        {
            $settingJournal = SettingJournal::where('feature', $feature)->where('name', $name)->first();

            if (! $settingJournal->chart_of_account_id) {
                throw new \App\Exceptions\PostingJournalAccountNotFound($feature, $name);
            }

            return $settingJournal->chart_of_account_id;
        }
    }
}
