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
            return $query->paginate(100);
        }

        // limit call maximum 1000 item per page
        $limit = $limit > 1000000 ? 1000000 : $limit;

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
     * Check if variable is exists.
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
     * @throws \App\Exceptions\PostingJournalAccountNotFound
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

if (! function_exists('convert_javascript_object_to_array')) {
    /**
     * If values is javascript object then convert it to array.
     *
     * @param $values
     * @return array
     */
    function convert_javascript_object_to_array($values)
    {
        if (is_null($values)) {
            return [];
        }

        if (! is_array($values)) {
            return json_decode($values, true);
        }

        return $values;
    }
}

if (! function_exists('base64_to_jpeg')) {
    /**
     * convert base64 to jpg file.
     *
     * @param $base64_string
     * @param $output_file
     * @return array
     */
    function base64_to_jpeg($base64_string, $output_file)
    {
        // open the output file for writing
        $ifp = fopen($output_file, 'wb');

        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode(',', $base64_string);

        // we could add validation here with ensuring count( $data ) > 1
        fwrite($ifp, base64_decode($data[1]));

        // clean up the file resource
        fclose($ifp);

        return $output_file;
    }


    if (! function_exists('str_clean')) {
        /**
         * Log an object / array.
         *
         * @param $str
         * @return string
         */
        function str_clean($str)
        {   
            return trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $str)));
        }
    }
}

if (! function_exists('response_error')) {
    /**
     * Get Tenant User.
     *
     * @param $userId
     *
     * @return \App\Model\Master\User
     */
    function response_error($error)
    {
        $code = $error->getCode();
        $message = $error->getMessage();
        
        $httpCode = 500;
        if($code !== 0) $httpCode = $code;

        return response (['code' => $code, 'message' => $message], $httpCode);
    }
}
