<?php

namespace App\Model;

use App\Model\Accounting\Journal;
use App\Model\Inventory\Inventory;
use App\Model\Master\User;
use App\Model\Master\Customer;
use App\Model\Master\Supplier;

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
        if (optional(auth()->user())->id) {
            $this->updated_by = optional(auth()->user())->id;

            if (! $this->exists) {
                $this->created_by = optional(auth()->user())->id;
            }
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

    public function fillData($data, $transaction)
    {
        $defaultNumberPostfix = '{y}{m}{increment=4}';

        $this->fill($data);
        $this->formable_id = $transaction->id;
        $this->formable_type = get_class($transaction);
        $this->generateFormNumber(
            $data['number'] ?? $transaction->defaultNumberPrefix.$defaultNumberPostfix,
            $transaction->customer_id,
            $transaction->supplier_id
        );
        $this->save();
    }

    public static function archive($form)
    {
        // Archive form number
        $form->edited_number = $form->number;
        $form->number = null;
        $form->save();

        // Remove relationship with journal and inventory
        Inventory::where('form_id', $form->id)->delete();
        Journal::where('form_id', $form->id)->orWhere('form_reference_id', $form->id)->delete();

        return $form;
    }

    public static function cancel($form)
    {
        // Cancel form
        $form->canceled = true;
        $form->save();

        // Remove relationship with journal and inventory
        Inventory::where('form_id', $form->id)->delete();
        Journal::where('form_id', $form->id)->orWhere('form_reference_id', $form->id)->delete();

        return $form;
    }

    /**
     * @param $formatNumber
     * @param null $customerId
     * @param null $supplierId
     *
     * {customerId=4} - 4 is for pad a string to 4 digit (0001)
     * {supplierId=4} - 4 is for pad a string to 4 digit (0001)
     * {code_customer} - customer code
     * {code_supplier} - supplier code
     *
     * use [] to convert int into roman number
     * example :
     * PO/{Y}-{m}-{d}/{increment=3} => PO/2018-12-26/001
     * PO/{Y}/[{m}]/{increment=4} => PO/2018/XII/0001
     */
    public function generateFormNumber($formatNumber, $customerId = null, $supplierId = null)
    {
        $this->number = $formatNumber;

        $this->convertTemplateDate();
        $this->convertTemplateIncrement();
        $this->convertTemplateMasterId('/{customerId=(\d)}/', $customerId);
        $this->convertTemplateMasterId('/{supplierId=(\d)}/', $supplierId);
        $this->convertTemplateCodeCustomer($customerId);
        $this->convertTemplateCodeSupplier($supplierId);
        $this->convertTemplateRoman();
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
     * {U} - The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
     *
     * @return mixed
     */
    private function convertTemplateDate()
    {
        preg_match_all('/{([a-zA-Z])}/', $this->number, $regexResult);
        foreach ($regexResult[0] as $key => $value) {
            $code = $regexResult[1][$key];
            $this->number = str_replace($value, date($code, strtotime($this->date)), $this->number);
        }
    }

    /**
     * @return mixed
     *
     * Example:
     * {increment=4} - 4 is for pad a string to 4 digits (0001)
     */
    private function convertTemplateIncrement()
    {
        preg_match_all('/{increment=(\d)}/', $this->number, $regexResult);
        if (! empty($regexResult)) {
            $increment = self::where('formable_type', $this->formable_type)
                ->whereNotNull('number')
                ->whereMonth('date', date('n', strtotime($this->date)))
                ->count();

            foreach ($regexResult[0] as $key => $value) {
                $padUntil = $regexResult[1][$key];
                $result = str_pad($increment + 1, $padUntil, '0', STR_PAD_LEFT);
                $this->number = str_replace($value, $result, $this->number);
            }
        }
    }

    private function convertTemplateRoman()
    {
        preg_match_all('/\[(\d+)\]/', $this->number, $regexResult);
        foreach ($regexResult[0] as $key => $value) {
            $num = $this->numberToRoman($regexResult[1][$key]);
            $this->number = str_replace($value, $num, $this->number);
        }
    }

    private function convertTemplateCodeCustomer($customerId)
    {
        $pattern = '{code_customer}';
        if (strpos($this->number, $pattern) !== false) {
            $customer = Customer::findOrFail($customerId);
            $this->number = str_replace($pattern, $customer->code, $this->number);
        }
    }

    private function convertTemplateCodeSupplier($supplierId)
    {
        $pattern = '{code_supplier}';
        if (strpos($this->number, $pattern) !== false) {
            $supplier = Supplier::findOrFail($supplierId);
            $this->number = str_replace($pattern, $supplier->code, $this->number);
        }
    }

    /**
     * Roman converter.
     * @param $integer
     * @return string
     */
    private function numberToRoman($integer)
    {
        $table = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $return = '';
        while ($integer > 0) {
            foreach ($table as $key => $value) {
                if ($integer >= $value) {
                    $integer -= $value;
                    $return .= $key;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * Convert masterId and add zero pads to the left.
     *
     * @param $pattern
     * @param $masterId
     *
     * @return string
     */
    private function convertTemplateMasterId($pattern, $masterId)
    {
        preg_match_all($pattern, $this->number, $regexResult);
        foreach ($regexResult[0] as $key => $value) {
            $padUntil = $regexResult[1][$key];
            $result = str_pad($masterId, $padUntil, '0', STR_PAD_LEFT);
            $this->number = str_replace($value, $result, $this->number);
        }
    }
}
