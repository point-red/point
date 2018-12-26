<?php

namespace App\Model;

use App\Model\Master\User;

class Form extends PointModel
{
    protected $connection = 'tenant';

    protected $user_logs = true;

    protected $fillable = [
        'date',
        'notes',
        'done',
        'approved',
    ];

    public function save(array $options = [])
    {
        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesUserLogs()) {
            $this->updateUserLog();
        }

        return parent::save();
    }

    /**
     * Determine if the model uses logs.
     *
     * @return bool
     */
    public function usesUserLogs()
    {
        return $this->user_logs;
    }

    public function updateUserLog()
    {
        $this->updated_by = optional(auth()->user())->id;

        if (!$this->exists) {
            $this->created_by = optional(auth()->user())->id;
        }
    }

    /**
     * The approvals that belong to the form.
     */
    public function approval()
    {
        return $this->hasMany(FormApproval::class);
    }

    /**
     * Get all of the owning formable models.
     */
    public function formable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * PHP date: https://www.w3schools.com/php/func_date_date_format.asp
     * {d} - The day of the month (from 01 to 31)
     * {j} - The day of the month without leading zeros (1 to 31)
     * {D} - A textual representation of a day (three letters) (Mon - Sun)
     * {l} (lowercase 'L') - A full textual representation of a day (Monday - Sunday)
     * {N} - The ISO-8601 numeric representation of a day (1 for Monday, 7 for Sunday)
     * {S} - The English ordinal suffix for the day of the month (2 characters st, nd, rd or th. Works well with j)
     * {w} - A numeric representation of the day (0 for Sunday, 6 for Saturday)
     * {z} - The day of the year (from 0 through 365)
     * {W} - The ISO-8601 week number of year (weeks starting on Monday)
     * {F} - A full textual representation of a month (January through December)
     * {m} - A numeric representation of a month (from 01 to 12)
     * {M} - A short textual representation of a month (three letters)
     * {n} - A numeric representation of a month, without leading zeros (1 to 12)
     * {t} - The number of days in the given month
     * {L} - Whether it's a leap year (1 if it is a leap year, 0 otherwise)
     * {o} - The ISO-8601 year number
     * {Y} - A four digit representation of a year
     * {y} - A two digit representation of a year
     * {a} - Lowercase am or pm
     * {A} - Uppercase AM or PM
     * {B} - Swatch Internet time (000 to 999)
     * {g} - 12-hour format of an hour (1 to 12)
     * {G} - 24-hour format of an hour (0 to 23)
     * {h} - 12-hour format of an hour (01 to 12)
     * {H} - 24-hour format of an hour (00 to 23)
     * {i} - Minutes with leading zeros (00 to 59)
     * {s} - Seconds, with leading zeros (00 to 59)
     * {u} - Microseconds (added in PHP 5.2.2)
     * {e} - The timezone identifier (Examples: UTC, GMT, Atlantic/Azores)
     * {I} (capital i) - Whether the date is in daylights savings time (1 if Daylight Savings Time, 0 otherwise)
     * {O} - Difference to Greenwich time (GMT) in hours (Example: +0100)
     * {P} - Difference to Greenwich time (GMT) in hours:minutes (added in PHP 5.1.3)
     * {T} - Timezone abbreviations (Examples: EST, MDT)
     * {Z} - Timezone offset in seconds. The offset for timezones west of UTC is negative (-43200 to 50400)
     * {c} - The ISO-8601 date (e.g. 2013-05-05T16:34:42+00:00)
     * {r} - The RFC 2822 formatted date (e.g. Fri, 12 Apr 2013 12:01:05 +0200)
     * {U} - The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
     * 
     * Point custom format:
     * {inc=4} - increment with at least n fixed digit, 0/1 = no padding zero
     * {cus=4} - customer_id with at least n fixed digits, 0/1 = no padding zero
     * {sup=4} - supplier_id with at least n fixed digits, 0/1 = no padding zero
     * {code_cus} - customer code
     * {code_sup} - supplier code
     * 
     * use [{}] to convert int into roman number
     * 
     * example
     * PO/{Y}-{m}-{d}/{inc=3}      =>   PO/2018-12-26/001
     * PO/{Y}/[{m}]/{inc=4}        =>   PO/2018/XII/0001
     */
    public function generateFormNumber($data)
    {
        $form_number = $data['number'];
        $time = strtotime($data['date']);

        $form_number = str_replace("{d}", Date('d', $time), $form_number);
        $form_number = str_replace("{j}", Date('j', $time), $form_number);
        $form_number = str_replace("{D}", Date('D', $time), $form_number);
        $form_number = str_replace("{l}", Date('l', $time), $form_number);
        $form_number = str_replace("{N}", Date('N', $time), $form_number);
        $form_number = str_replace("{S}", Date('S', $time), $form_number);
        $form_number = str_replace("{w}", Date('w', $time), $form_number);
        $form_number = str_replace("{z}", Date('z', $time), $form_number);
        $form_number = str_replace("{W}", Date('W', $time), $form_number);
        $form_number = str_replace("{F}", Date('F', $time), $form_number);
        $form_number = str_replace("{m}", Date('m', $time), $form_number);
        $form_number = str_replace("{M}", Date('M', $time), $form_number);
        $form_number = str_replace("{n}", Date('n', $time), $form_number);
        $form_number = str_replace("{t}", Date('t', $time), $form_number);
        $form_number = str_replace("{L}", Date('L', $time), $form_number);
        $form_number = str_replace("{o}", Date('o', $time), $form_number);
        $form_number = str_replace("{Y}", Date('Y', $time), $form_number);
        $form_number = str_replace("{y}", Date('y', $time), $form_number);
        $form_number = str_replace("{a}", Date('a', $time), $form_number);
        $form_number = str_replace("{A}", Date('A', $time), $form_number);
        $form_number = str_replace("{B}", Date('B', $time), $form_number);
        $form_number = str_replace("{g}", Date('g', $time), $form_number);
        $form_number = str_replace("{G}", Date('G', $time), $form_number);
        $form_number = str_replace("{h}", Date('h', $time), $form_number);
        $form_number = str_replace("{H}", Date('H', $time), $form_number);
        $form_number = str_replace("{i}", Date('i', $time), $form_number);
        $form_number = str_replace("{s}", Date('s', $time), $form_number);
        $form_number = str_replace("{u}", Date('u', $time), $form_number);
        $form_number = str_replace("{e}", Date('e', $time), $form_number);
        $form_number = str_replace("{I}", Date('I', $time), $form_number);
        $form_number = str_replace("{O}", Date('O', $time), $form_number);
        $form_number = str_replace("{P}", Date('P', $time), $form_number);
        $form_number = str_replace("{T}", Date('T', $time), $form_number);
        $form_number = str_replace("{Z}", Date('Z', $time), $form_number);
        $form_number = str_replace("{c}", Date('c', $time), $form_number);
        $form_number = str_replace("{r}", Date('r', $time), $form_number);
        $form_number = str_replace("{U}", Date('U', $time), $form_number);

        // TODO how to get the increment $inc
        $patternInc = "/{inc=(\d)}/";
        preg_match_all($patternInc, $form_number, $arr);
        foreach ($arr[0] as $key => $value) {
            $padUntil = $arr[1][$key];
            // $inc = getIncrementNumber();
            $inc = rand(1,9999);
            $result = str_pad($inc, $padUntil, "0", STR_PAD_LEFT);
            $form_number = str_replace($value, $result, $form_number);
        }
        
        $patternCus = "/{cus=(\d)}/";
        preg_match_all($patternCus, $form_number, $arr);
        foreach ($arr[0] as $key => $value) {
            $padUntil = $arr[1][$key];
            $result = str_pad($data['customer_id'], $padUntil, "0", STR_PAD_LEFT);
            $form_number = str_replace($value, $result, $form_number);
        }
        
        $patternSup = "/{sup=(\d)}/";
        preg_match_all($patternSup, $form_number, $arr);
        foreach ($arr[0] as $key => $value) {
            $padUntil = $arr[1][$key];
            $result = str_pad($data['supplier_id'], $padUntil, "0", STR_PAD_LEFT);
            $form_number = str_replace($value, $result, $form_number);
        }

        if (strpos($form_number, '{code_cus}') !== false) {
            $customer = Customer::findOrFail($data['customer_id']);
            $form_number = str_replace("{code_cus}", $customer->code, $form_number);
        }

        if (strpos($form_number, '{code_sup}') !== false) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $form_number = str_replace("{code_sup}", $supplier->code, $form_number);
        }

        // TODO how to convert to roman number
        $patternRom = "/\[(\d+)\]/";
        preg_match_all($patternRom, $form_number, $arr);
        foreach ($arr[0] as $key => $value) {
            $num = $this->numberToRoman($arr[1][$key]);
            $form_number = str_replace($value, $num, $form_number);
        }

        $this->number = $form_number;
    }

    /**
     * Roman converter
     *
     * @param $integer
     *
     * @return string
     */
    private function numberToRoman($integer)
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }

        return $return;
    }
}
