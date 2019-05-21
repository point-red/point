<?php

namespace App\Model\HumanResource\Kpi;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Model\TransactionModel;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;

class Automated extends TransactionModel
{
    protected $connection = 'tenant';

    /**
     * Get the automated data based on indicator.
     */
    public static function getData($automated_code, $dateFrom, $dateTo, $employeeId)
    {
        $dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));

        $employee = Employee::findOrFail($employeeId);
        $userId = $employee->user_id ?? 0;

        $target = 0;
        $score = 0;

        $numberOfDays = self::getDays($dateFrom, $dateTo);

        if ($automated_code === 'C') {
            $target = SalesVisitationTarget::target($dateTo, $userId);
            $target = $target['call'] * $numberOfDays;
            $score = SalesVisitation::call($dateFrom, $dateTo, $userId);
        } elseif ($automated_code === 'EC') {
            $target = SalesVisitationTarget::target($dateTo, $userId);
            $target = $target['effective_call'] * $numberOfDays;
            $score = SalesVisitation::effectiveCall($dateFrom, $dateTo, $userId);
        } elseif ($automated_code === 'V') {
            $target = SalesVisitationTarget::target($dateTo, $userId);
            $target = $target['value'] * $numberOfDays;
            $score = SalesVisitation::value($dateFrom, $dateTo, $userId);
        }

        return ['score' => $score, 'target' => $target];
    }

    public static function getDays($dateFrom, $dateTo)
    {
        $dateTimeFrom = new DateTime($dateFrom);

        $dateTimeTo = new DateTime($dateTo);
        $dateTimeTo->modify('+1 day');

        $difference = $dateTimeFrom->diff($dateTimeTo);
        $numberOfDays = $difference->days;

        $period = new DatePeriod($dateTimeFrom, new DateInterval('P1D'), $dateTimeTo);

        $holidays = [];

        foreach ($period as $dt) {
            $currentDay = $dt->format('D');

            if ($currentDay == 'Sun') {
                $numberOfDays--;
            } elseif (in_array($dt->format('Y-m-d'), $holidays)) {
                $numberOfDays--;
            }
        }

        return $numberOfDays;
    }
}
